<?php

namespace Draw\Component\DataSynchronizer\Export;

use Draw\Component\DataSynchronizer\Artefact;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadataFactory;
use Draw\Component\DataSynchronizer\Metadata\SerializationFileDumper;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DataExporter
{
    public function __construct(
        #[Autowire(service: 'draw.data_synchronizer.serializer')]
        private Serializer $serializer,
        private EntitySynchronizationMetadataFactory $metadataFactory,
        private ObjectSelectorInterface $objectSelector,
        private SerializationFileDumper $serializationFileDumper,
    ) {
    }

    /**
     * Export data to a zip, return the file path of the zip
     */
    public function export(): string
    {
        $file = tempnam(sys_get_temp_dir(), 'export-').'.zip';

        $artefact = Artefact::createFromFile($file);

        foreach ($this->metadataFactory->getAllEntitySynchronizationMetadata() as $extractionMetadata) {
            $this->serializationFileDumper->generateSerializerFile($extractionMetadata);

            $entities = $this->objectSelector->select($extractionMetadata);

            if (null === $entities) {
                continue;
            }

            $artefact->addClassData(
                $extractionMetadata->classMetadata->name,
                $this->serializer->toArray(
                    $entities,
                    SerializationContext::create()
                        ->setSerializeNull(true)
                        ->setGroups('Default')
                )
            );
        }

        $artefact->finalize();

        return $file;
    }
}
