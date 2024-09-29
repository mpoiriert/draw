<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Vendor;
use Draw\Component\OpenApi\Schema\VendorExtensionSupportInterface;

class VendorAttributeExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof VendorExtensionSupportInterface) {
            return false;
        }

        if (
            !$source instanceof \ReflectionMethod
            && !$source instanceof \ReflectionProperty
            && !$source instanceof \ReflectionClass
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param \ReflectionMethod|\ReflectionClass|\ReflectionProperty $source
     * @param VendorExtensionSupportInterface                        $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach ($this->getAttributes($source) as $attribute) {
            $target->setVendorDataKey($attribute->getVendorName(), $attribute);
        }
    }

    /**
     * @return Vendor[]
     */
    private function getAttributes(\ReflectionMethod|\ReflectionClass|\ReflectionProperty $reflector): array
    {
        $classLevelAttributes = [];
        $attributes = $reflector->getAttributes(Vendor::class, \ReflectionAttribute::IS_INSTANCEOF);

        if ($reflector instanceof \ReflectionMethod || $reflector instanceof \ReflectionProperty) {
            $classLevelAttributes = $reflector
                ->getDeclaringClass()
                ->getAttributes(Vendor::class, \ReflectionAttribute::IS_INSTANCEOF)
            ;
        }

        $classLevelAttributes = array_map(
            static fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            $classLevelAttributes
        );

        $attributes = array_map(
            static fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            $attributes
        );

        return $this->mergeWithClassAnnotations($attributes, $classLevelAttributes);
    }

    /**
     * @param Vendor[] $currentAttributes
     * @param Vendor[] $classAttributes
     *
     * @return Vendor[]
     */
    private function mergeWithClassAnnotations(array $currentAttributes, array $classAttributes): array
    {
        $classAttributes = array_filter(
            $classAttributes,
            fn (Vendor $classAnnotation) => !$this->alreadyPresentIn($classAnnotation, $currentAttributes)
        );

        $classAttributes = array_map(
            static fn (Vendor $annotation) => clone $annotation,
            $classAttributes
        );

        return array_merge($classAttributes, $currentAttributes);
    }

    /**
     * @param Vendor[] $currentAttributes
     */
    private function alreadyPresentIn(Vendor $attribute, array $currentAttributes): bool
    {
        foreach ($currentAttributes as $currentAttribute) {
            if ($currentAttribute->getVendorName() === $attribute->getVendorName()) {
                return true;
            }
        }

        return false;
    }
}
