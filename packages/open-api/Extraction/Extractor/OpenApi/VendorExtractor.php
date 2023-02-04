<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\VendorExtensionSupportInterface;
use Draw\Component\OpenApi\Schema\VendorInterface;

class VendorExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function __construct(private Reader $annotationReader)
    {
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        return match (true) {
            !$target instanceof VendorExtensionSupportInterface => false,
            $source instanceof \ReflectionMethod, $source instanceof \ReflectionClass, $source instanceof \ReflectionProperty => true,
            default => false,
        };
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

        foreach ($this->getAnnotations($source) as $annotation) {
            $target->setVendorDataKey($annotation->getVendorName(), $annotation);
        }
    }

    /**
     * @return VendorInterface[]
     */
    private function getAnnotations(\ReflectionMethod|\ReflectionClass|\ReflectionProperty $reflector): array
    {
        $classLevelAnnotations = [];
        $annotations = [];
        switch (true) {
            case $reflector instanceof \ReflectionMethod:
                $annotations = $this->annotationReader->getMethodAnnotations($reflector);
                $classLevelAnnotations = $this->annotationReader->getClassAnnotations($reflector->getDeclaringClass());
                break;
            case $reflector instanceof \ReflectionProperty:
                $annotations = $this->annotationReader->getPropertyAnnotations($reflector);
                $classLevelAnnotations = $this->annotationReader->getClassAnnotations($reflector->getDeclaringClass());
                break;
            case $reflector instanceof \ReflectionClass:
                $annotations = $this->annotationReader->getClassAnnotations($reflector);
                break;
        }

        $filter = fn ($annotation) => $annotation instanceof VendorInterface;

        $classLevelAnnotations = array_filter($classLevelAnnotations, $filter);
        $annotations = array_filter($annotations, $filter);

        return $this->mergeWithClassAnnotations($annotations, $classLevelAnnotations);
    }

    /**
     * @param array|VendorInterface[] $currentAnnotations
     * @param array|VendorInterface[] $classAnnotations
     *
     * @return array|VendorInterface[]
     */
    private function mergeWithClassAnnotations(array $currentAnnotations, array $classAnnotations): array
    {
        $classAnnotations = array_filter(
            $classAnnotations,
            fn (VendorInterface $classAnnotation) => match (true) {
                !$classAnnotation->allowClassLevelConfiguration(), $this->alreadyPresentIn($classAnnotation, $currentAnnotations) => false,
                default => true,
            }
        );

        $classAnnotations = array_map(
            fn (VendorInterface $annotation) => clone $annotation,
            $classAnnotations
        );

        return array_merge($classAnnotations, $currentAnnotations);
    }

    /**
     * @param array|VendorInterface[] $currentAnnotations
     */
    private function alreadyPresentIn(VendorInterface $annotation, array $currentAnnotations): bool
    {
        foreach ($currentAnnotations as $currentAnnotation) {
            if ($currentAnnotation->getVendorName() === $annotation->getVendorName()) {
                return true;
            }
        }

        return false;
    }
}
