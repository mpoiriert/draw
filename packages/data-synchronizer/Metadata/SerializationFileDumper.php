<?php

namespace Draw\Component\DataSynchronizer\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DataSynchronizer\Serializer\Metadata\Driver\DoctrineTypeDriver;
use Metadata\Driver\FileLocator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class SerializationFileDumper
{
    private Filesystem $filesystem;

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private EntitySynchronizationMetadataFactory $metadataFactory,
        #[Autowire(service: 'draw.data_synchronizer.serializer.metadata.file_locator')]
        private FileLocator $fileLocator,
        private DoctrineTypeDriver $doctrineTypeDriver,
        #[Autowire('%draw.data_synchronizer.metadata_directory%')]
        private string $metadataDir,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function generateAllSerializerFiles(): void
    {
        foreach ($this->metadataFactory->getAllEntitySynchronizationMetadata() as $metadata) {
            $this->generateSerializerFile($metadata, true);
        }
    }

    public function generateSerializerFile(
        EntitySynchronizationMetadata $metadata,
        bool $force = false,
    ): void {
        $this->doGenerateSerializerFile($metadata, $force);
    }

    private function writeFile(string $fileName, string $class, array $data): void
    {
        $this->filesystem
            ->dumpFile(
                $fileName,
                str_replace('{  }', '{ }', Yaml::dump([$class => $data], 10))
            )
        ;
    }

    /**
     * @param string[] $classes
     */
    private function getMetaDataInfoForField(string $fieldName, array $classes): ?ClassMetadata
    {
        foreach ($classes as $class) {
            if (!$manager = $this->managerRegistry->getManagerForClass($class)) {
                continue;
            }

            \assert($manager instanceof EntityManagerInterface);
            $metaDataInfo = $manager->getClassMetadata($class);

            if ($metaDataInfo->hasField($fieldName) || $metaDataInfo->hasAssociation($fieldName)) {
                return $metaDataInfo;
            }
        }

        return null;
    }

    private function doGenerateSerializerFile(EntitySynchronizationMetadata $metadata, bool $force, array $classHierarchy = []): void
    {
        $class = $metadata->classMetadata->name;
        $classHierarchy = array_merge([$metadata->classMetadata->name], $classHierarchy);
        $parentClass = get_parent_class($metadata->classMetadata->name);
        if (false !== $parentClass) {
            $this->doGenerateSerializerFile(
                $this->metadataFactory->getEntitySynchronizationMetadata($parentClass),
                $force,
                $classHierarchy,
            );
        }

        if (!$force && $this->fileLocator->findFileForClass(new \ReflectionClass($class), 'yaml')) {
            return;
        }

        $fileName = $this->metadataDir.\DIRECTORY_SEPARATOR.str_replace('\\', '.', $class).'.yaml';

        $properties = [];
        $reflectionClass = new \ReflectionClass($class);
        $metadataFactory = $this->managerRegistry->getManager()->getMetadataFactory();

        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            if (
                $reflectionClass->getName() !== $property->getDeclaringClass()->getName()
                && false !== $parentClass
                && !$metadataFactory->isTransient($parentClass)
            ) {
                // We don't want to export properties from parent classes that are not transient.
                continue;
            }

            $classMetadata = $this->getMetaDataInfoForField($property->getName(), $classHierarchy);
            if (!$classMetadata instanceof ClassMetadata) {
                // The property is not a doctrine field, we don't want to export it.
                continue;
            }

            if ($classMetadata->isAssociationInverseSide($property->getName())) {
                // The property is an inverse side of a relation, we don't want to export it.
                continue;
            }

            if ($classMetadata->hasAssociation($property->getName())
                && null === $this->metadataFactory->getEntitySynchronizationMetadata($classMetadata->getAssociationTargetClass($property->getName()))
            ) {
                // target class is not synchronizable, therefore we're not going to export this relation.
                continue;
            }

            $name = $property->getName();
            $properties[$name] = [
                'expose' => !\in_array($name, $metadata->excludeFields, true),
                'type' => $this->doctrineTypeDriver->getType($classMetadata, $name),
            ];
        }

        $this->writeFile($fileName, $class, ['exclusion_policy' => 'ALL'] + compact('properties'));
    }
}
