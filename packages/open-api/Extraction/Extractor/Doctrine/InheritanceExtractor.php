<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Schema;

class InheritanceExtractor implements ExtractorInterface
{
    public const VENDOR_DATA_DOCTRINE_ROOT_ENTITY_CLASS = 'X-DrawOpenApi-DoctrineRootEntityClass';
    public const VENDOR_DATA_DOCTRINE_ENTITY_CLASS = 'X-DrawOpenApi-DoctrineEntityClass';
    public const VENDOR_DATA_DOCTRINE_IS_ROOT_ENTITY = 'X-DrawOpenApi-DoctrineIsRootEntity';

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionClass) {
            return false;
        }

        if (!$target instanceof Schema) {
            return false;
        }

        if (!$this->managerRegistry->getManagerForClass($source->name)) {
            return false;
        }

        return true;
    }

    /**
     * @param \ReflectionClass $source
     * @param Schema           $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $metaData = $this->managerRegistry->getManagerForClass($source->name)->getClassMetadata($source->name);
        if (!$metaData instanceof ClassMetadataInfo) {
            return;
        }

        $target->setVendorDataKey(self::VENDOR_DATA_DOCTRINE_ENTITY_CLASS, $source->name);

        if ($metaData->isInheritanceTypeNone()) {
            return;
        }

        $target->setVendorDataKey(self::VENDOR_DATA_DOCTRINE_ENTITY_CLASS, $source->name);

        $openApi = $extractionContext->getOpenApi();

        if ($metaData->isRootEntity()) {
            $target->setVendorDataKey(self::VENDOR_DATA_DOCTRINE_IS_ROOT_ENTITY, true);
            $target->discriminator = $metaData->discriminatorColumn['name'];
            $target->required[] = $target->discriminator;
            foreach ($metaData->discriminatorMap as $class) {
                $schema = new Schema();
                $schema->setVendorDataKey(self::VENDOR_DATA_DOCTRINE_ROOT_ENTITY_CLASS, $source->name);
                $openApi->extract($class, $schema, $extractionContext);
            }
            $target->properties[$metaData->discriminatorColumn['name']] = $property = new Schema();
            $property->type = 'string';
            $property->description = 'The concrete class of the inheritance.';
            $property->enum = array_keys($metaData->discriminatorMap);
        } else {
            if (isset($target->properties[$metaData->discriminatorColumn['name']])) {
                $property = $target->properties[$metaData->discriminatorColumn['name']];
                $property->description = 'Discriminator property. Value will be ';
                $property->type = 'string';
                $property->enum = [$metaData->discriminatorValue];
            }
        }
    }
}
