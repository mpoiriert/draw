<?php

namespace Draw\Component\DataSynchronizer\Import;

use Draw\Component\DataSynchronizer\Artefact;
use Draw\Component\DataSynchronizer\Export\DataExporter;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadataFactory;

class ImportationContextFactory
{
    public function __construct(
        private EntitySynchronizationMetadataFactory $importMetadataReader,
        private ObjectDataComparator                 $objectDataComparator,
        private DataExporter                         $dataExporter,
    )
    {
    }

    public function create(string $file): ImportationContext
    {
        $importArtefact = Artefact::loadFromFile($file);

        $internalFile = $this->dataExporter->export();

        register_shutdown_function('unlink', $internalFile);

        $internalArtefact = Artefact::loadFromFile($internalFile);

        $this->prepare(
            $importationContext = new ImportationContext(),
            $importArtefact,
            $internalArtefact
        );

        $importArtefact->close();
        $internalArtefact->close();

        return $importationContext;
    }

    private function prepare(ImportationContext $restorationContext, Artefact $importArtefact, Artefact $internalArtefact): void
    {
        foreach ($importArtefact->getExtractionDataFiles() as $file) {
            $this->doPrepare(
                $this->mapClassLookUpData(
                    $importArtefact->jsonDecodeFromName($file),
                ),
                $this->mapClassLookUpData(
                    $internalArtefact->jsonDecodeFromName($file)
                ),
                $restorationContext
            );
        }
    }

    private function doPrepare(array $external, array $internal, ImportationContext $restorationContext): void
    {
        // Contains all the new entities or changed entities
        $diff = [];

        foreach ($external as $className => $entities) {
            $diff[$className] ??= [];

            foreach ($entities as $lookUp => $entityData) {
                // If the entity doesn't exist in the internal data, we add it to the diff
                if (!isset($internal[$className][$lookUp])) {
                    $diff[$className][$lookUp] = $entityData;

                    continue;
                }

                // We compare entities, if they are not the same, we add it to the diff
                if (!$this->objectDataComparator->isSame($entityData, $internal[$className][$lookUp])) {
                    $diff[$className][$lookUp] = $entityData;
                }

                // We remove it from the internal data, everything that is left in the internal data is to be deleted
                unset($internal[$className][$lookUp]);
            }
        }

        $lateEntities = [];
        foreach ($diff as $class => $entities) {
            $metadata = $this->importMetadataReader->getEntitySynchronizationMetadata($class);

            // Some time we have a circular reference between entity
            // the late process properties are reference to other entities
            // they will be processed as a second pass so the entities exists
            if ($metadata->lateProcessFields) {
                foreach ($entities as $index => $entityData) {
                    $lateEntity = null;

                    foreach ($metadata->lateProcessFields as $property) {
                        if ($entityData[$property] ?? null) {
                            continue;
                        }

                        if (\is_array($entityData[$property]) && 0 === \count($entityData[$property])) {
                            continue;
                        }

                        // We need at least the look-up key to find the entity again
                        $lateEntity ??= array_intersect_key(
                            $entityData,
                            array_flip($metadata->lookUpFields)
                        );

                        // We add the late entity property
                        $lateEntity[$property] = $entityData[$property];

                        // We remove the properties that will be processed later
                        unset($diff[$class][$index][$property]);
                    }

                    if (null !== $lateEntity) {
                        $lateEntities[$class][] = $lateEntity;
                    }
                }
            }

            $restorationContext->addRestorationData(new RestorationData($class, array_values($entities)));
        }

        foreach ($lateEntities as $class => $entities) {
            $restorationContext->addLateRestorationData(new RestorationData($class, $entities));
        }


        // We prepare which entity must be deleted
        foreach ($internal as $class => $entities) {
            $metadata = $this->importMetadataReader->getEntitySynchronizationMetadata($class);
            if (!$metadata->purge) {
                continue;
            }

            $restorationContext->addToDelete(
                new RestorationData(
                    $class,
                    // To be more efficient we just track the look-up field since we just want to load the entity
                    array_map(
                        fn(array $entityData) => array_intersect_key(
                            $entityData,
                            array_flip($metadata->lookUpFields)
                        ),
                        $entities
                    )
                )
            );
        }
    }

    /**
     * Return an associative array of class names and object data with the look-up field as the key.
     *
     * @return array<class-string, array<string, array<string, mixed>>>
     */
    private function mapClassLookUpData(array $data): array
    {
        $classes = [];

        foreach ($data as $className => $entityData) {
            $classes[$className] ??= [];

            $lookUpFields = $this->importMetadataReader
                ->getEntitySynchronizationMetadata($className)
                ->lookUpFields;

            foreach ($entityData as $entity) {
                $lookUp = [];
                foreach ($lookUpFields as $field) {
                    if (!\array_key_exists($field, $entity)) {
                        throw new \RuntimeException('Field ' . $field . ' not found in entity ' . json_encode($entity, \JSON_THROW_ON_ERROR));
                    }
                    $lookUp[$field] = $entity[$field];
                }

                $classes[$className][serialize($lookUp)] = $entity;
            }
        }

        return $classes;
    }
}
