<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;

class SerializationConfigurationExtractor implements ExtractorInterface
{
    private Reader $annotationReader;

    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
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
     * @param \ReflectionMethod $source
     * @param Schema            $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
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
            $response = $extractionContext->getParameter('response');
            if ($response) {
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

    private function getSerialization(\ReflectionMethod $reflectionMethod): ?Serialization
    {
        return $this->annotationReader->getMethodAnnotation($reflectionMethod, Serialization::class);
    }
}
