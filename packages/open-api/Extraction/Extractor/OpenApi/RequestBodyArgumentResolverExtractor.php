<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBody;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Schema;

class RequestBodyArgumentResolverExtractor implements ExtractorInterface
{
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionMethod) {
            return false;
        }

        if (!$target instanceof Operation) {
            return false;
        }

        if (!$this->getRequestBodyAttribute($source)) {
            return false;
        }

        return true;
    }

    /**
     * @param \ReflectionMethod $source
     * @param Operation         $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $attribute = $this->getRequestBodyAttribute($source);

        $target->parameters[] = $parameter = new BodyParameter();

        $serializationGroups = $attribute->deserializationGroups;
        $validationGroups = $attribute->validationGroups;

        $subContext = $extractionContext->createSubContext();
        $modelContext = $subContext->getParameter('model-context', []);

        if ($serializationGroups) {
            $modelContext['serializer-groups'] = $serializationGroups;
        }

        if ($validationGroups) {
            $modelContext['validation-groups'] = $validationGroups;
        }

        $subContext->setParameter('model-context', $modelContext);

        $subContext->getOpenApi()->extract(
            $attribute->type,
            $parameter->schema = new Schema(),
            $subContext
        );
    }

    private function getRequestBodyAttribute(\ReflectionMethod $reflectionMethod): ?RequestBody
    {
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $attribute = $parameter->getAttributes(RequestBody::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

            $attribute = $attribute?->newInstance();

            if (!$attribute instanceof RequestBody) {
                continue;
            }

            $options = $attribute->options;

            if (isset($options['draw_open_api']['disable']) && $options['draw_open_api']['disable']) {
                continue;
            }

            if (null === $attribute->type) {
                $parameterType = $parameter->getType();
                if (!$parameterType instanceof \ReflectionNamedType) {
                    throw new \RuntimeException('Unable to extract the type of the parameter ['.$parameter->getName().'] of the method ['.$reflectionMethod->getName().']');
                }

                $attribute->type = $parameterType->getName();
            }

            return $attribute;
        }

        return null;
    }
}
