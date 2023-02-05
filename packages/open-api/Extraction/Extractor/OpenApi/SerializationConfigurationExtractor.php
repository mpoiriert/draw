<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Component\OpenApi\Serializer\Serialization;

class SerializationConfigurationExtractor implements ExtractorInterface
{
    public static function getDefaultPriority(): int
    {
        return 128;
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

        $serialization = $this->getSerialization($extractionContext->getParameter('controller-reflection-method'));

        if (null === $serialization) {
            return;
        }

        $groups = $serialization->serializerGroups;

        if ($statusCode = $serialization->statusCode) {
            $extractionContext->setParameter('response-status-code', $statusCode);
        }

        if (!empty($groups)) {
            $modelContext = $extractionContext->getParameter('model-context', []);
            $modelContext['serializer-groups'] = $groups;
            $extractionContext->setParameter('model-context', $modelContext);
        }
    }

    private function getSerialization(\ReflectionMethod $reflectionMethod): ?Serialization
    {
        $attribute = $reflectionMethod
            ->getAttributes(Serialization::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

        return $attribute?->newInstance();
    }
}
