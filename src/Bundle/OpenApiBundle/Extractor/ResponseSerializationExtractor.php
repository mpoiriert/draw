<?php

namespace Draw\Bundle\OpenApiBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Bundle\OpenApiBundle\Response\Serialization;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;
use ReflectionMethod;

class ResponseSerializationExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $target
     *
     * @return bool
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$target instanceof Schema) {
            return false;
        }

        if (!$extractionContext->hasParameter('controller-reflection-method')) {
            return false;
        }

        if (!$this->getSerialization($extractionContext->getParameter('controller-reflection-method'))) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionMethod $source
     * @param Schema           $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $groups = [];

        if ($serialization = $this->getSerialization($extractionContext->getParameter('controller-reflection-method'))) {
            $groups = $serialization->getSerializerGroups();
            if ($statusCode = $serialization->getStatusCode()) {
                $extractionContext->setParameter('response-status-code', $statusCode);
            }

            /** @var Response $response */
            if ($response = $extractionContext->getParameter('response')) {
                foreach ($serialization->getHeaders() as $name => $header) {
                    $response->headers[$name] = $header;
                }
            }
        }

        if (!empty($groups)) {
            $modelContext = $extractionContext->getParameter('model-context', []);
            $modelContext['serializer-groups'] = $groups;
            $extractionContext->setParameter('model-context', $modelContext);
        }
    }

    /**
     * @return Serialization|null
     */
    private function getSerialization(ReflectionMethod $reflectionMethod)
    {
        /** @var Serialization|null $serialization */
        $serialization = $this->annotationReader->getMethodAnnotation($reflectionMethod, Serialization::class);

        return $serialization;
    }
}
