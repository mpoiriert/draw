<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\VendorExtensionSupportInterface;
use Draw\Component\OpenApi\Schema\VendorInterface;
use Reflector;
use RuntimeException;

class VendorExtractor implements ExtractorInterface
{
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$target instanceof VendorExtensionSupportInterface) {
            return false;
        }

        if (!$source instanceof Reflector) {
            return false;
        }

        if (!method_exists($source, 'getDocComment')) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed                                 $source
     * @param mixed|VendorExtensionSupportInterface $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach ($this->getAnnotations($source) as $annotation) {
            $target->setVendorDataKey($annotation->getVendorName(), $annotation);
        }
    }

    /**
     * @return array|VendorInterface[]
     */
    private function getAnnotations(Reflector $reflector): array
    {
        $classLevelAnnotations = [];
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
            default:
                throw new RuntimeException('Not supported reflection class ['.get_class($reflector).']');
                break;
        }

        $filter = function ($annotation) {
            return $annotation instanceof VendorInterface;
        };

        $classLevelAnnotations = array_filter($classLevelAnnotations, $filter);
        $annotations = array_filter($annotations, $filter);

        return $this->mergeWithClassAnnotations($annotations, $classLevelAnnotations);
    }

    /**
     * @param array|VendorInterface[] $currentAnnotations
     * @param array|VendorInterface[] $classAnnotations
     */
    private function mergeWithClassAnnotations(array $currentAnnotations, array $classAnnotations): array
    {
        $classAnnotations = array_filter($classAnnotations,
            function (VendorInterface $classAnnotation) use ($currentAnnotations) {
                switch (true) {
                    case !$classAnnotation->allowClassLevelConfiguration():
                    case $this->alreadyPresentIn($classAnnotation, $currentAnnotations):
                        return false;
                }

                return true;
            });

        $classAnnotations = array_map(
            function (VendorInterface $annotation) {
                return clone $annotation;
            },
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
