<?php namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Vendor;
use Draw\Component\OpenApi\Schema\VendorExtensionSupportInterface;
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
     * @param mixed $source
     * @param mixed|VendorExtensionSupportInterface $target
     * @param ExtractionContextInterface $extractionContext
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach($this->getAnnotations($source) as $annotation) {
            $target->setVendorDataKey($annotation->name, $annotation);
        }
    }

    /**
     * @param Reflector $reflector
     * @return array|Vendor[]
     */
    private function getAnnotations(Reflector $reflector): array
    {
        switch (true) {
            case $reflector instanceof \ReflectionMethod:
                $annotations = $this->annotationReader->getMethodAnnotations($reflector);
                break;
            case $reflector instanceof \ReflectionProperty:
                $annotations = $this->annotationReader->getPropertyAnnotations($reflector);
                break;
            case $reflector instanceof \ReflectionClass:
                $annotations = $this->annotationReader->getClassAnnotations($reflector);
                break;
            default:
                throw new RuntimeException('Not supported reflection class [' . get_class($reflector) . ']');
                break;

        }

        return array_filter($annotations, function ($annotation) {
            return $annotation instanceof Vendor;
        });
    }
}