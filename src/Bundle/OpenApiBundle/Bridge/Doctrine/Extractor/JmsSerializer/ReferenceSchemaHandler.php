<?php namespace Draw\Bundle\OpenApiBundle\Bridge\Doctrine\Extractor\JmsSerializer;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Metadata\PropertyMetadata;
use Doctrine\Persistence\ManagerRegistry;

class ReferenceSchemaHandler implements TypeToSchemaHandlerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function extractSchemaFromType(
        PropertyMetadata $propertyMetadata,
        ExtractionContextInterface $extractionContext
    ) {
        if (null === ($type = $this->getReferenceType($propertyMetadata))) {
            return null;
        }

        $propertySchema = new Schema();
        $propertySchema->type = $type;

        return $propertySchema;
    }

    private function getReferenceType(PropertyMetadata $item)
    {
        switch (true) {
            case !isset($item->type['name']):
            case $item->type['name'] != 'ObjectReference':
            case !isset($item->type['params'][0]['name']):
                return null;
        }

        $class = $item->type['params'][0]['name'];
        $metadataFor = $this->managerRegistry->getManagerForClass($class)
            ->getMetadataFactory()
            ->getMetadataFor($class);

        return $metadataFor->getTypeOfField($metadataFor->getIdentifierFieldNames()[0]);
    }
}