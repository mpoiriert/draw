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
    private Reader $annotationReader;

    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        switch (true) {
            case !$target instanceof VendorExtensionSupportInterface:
                return false;
            case $source instanceof \ReflectionMethod:
            case $source instanceof \ReflectionClass:
            case $source instanceof \ReflectionProperty:
                return true;
        }

        return false;
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
     * @param \ReflectionMethod|\ReflectionClass|\ReflectionProperty $reflector
     *
     * @return array|VendorInterface[]
     */
    private function getAnnotations($reflector): array
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
            function (VendorInterface $classAnnotation) use ($currentAnnotations) {
                switch (true) {
                    case !$classAnnotation->allowClassLevelConfiguration():
                    case $this->alreadyPresentIn($classAnnotation, $currentAnnotations):
                        return false;
                }

                return true;
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
