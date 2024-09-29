<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\PhpDoc;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BaseParameter;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Parameter;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Void_;

class OperationExtractor implements ExtractorInterface
{
    private ContextFactory $contextFactory;
    private DocBlockFactoryInterface $docBlockFactory;
    private array $exceptionResponseCodes = [];

    public static function getDefaultPriority(): int
    {
        return -128;
    }

    public function __construct(
        ?DocBlockFactoryInterface $docBlockFactory = null,
    ) {
        $this->contextFactory = new ContextFactory();
        $this->docBlockFactory = $docBlockFactory ?: DocBlockFactory::createInstance();
    }

    public function registerExceptionResponseCodes(string $exceptionClass, int $code = 500, ?string $message = null): void
    {
        $this->exceptionResponseCodes[$exceptionClass] = [$code, $message];
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionMethod) {
            return false;
        }

        if (!$target instanceof Operation) {
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

        try {
            $docBlock = $this->createDocBlock($source);
        } catch (\InvalidArgumentException) {
            return;
        }

        if (!$target->summary) {
            $target->summary = $docBlock->getSummary() ?: null;
        }

        if (!$target->description) {
            $target->description = (string) $docBlock->getDescription() ?: null;
        }

        if ($docBlock->getTagsByName('deprecated')) {
            $target->deprecated = true;
        }

        $this->extractResponse($docBlock, $extractionContext, $source, $target);
        $this->extractExceptionResponses($docBlock, $target);
        $this->extractParameters($docBlock, $target, $extractionContext);
    }

    private function createDocBlock(\Reflector $reflector): DocBlock
    {
        return $this->docBlockFactory
            ->create(
                $reflector,
                $this->contextFactory->createFromReflector($reflector)
            );
    }

    private function getExceptionInformation(\Exception $exception): array
    {
        foreach ($this->exceptionResponseCodes as $class => $information) {
            if ($exception instanceof $class) {
                return $information;
            }
        }

        return [500, null];
    }

    private function extractStatusCode(
        ?Type $type,
        Response $response,
        ExtractionContextInterface $extractionContext,
        \ReflectionMethod $source,
    ): int {
        if (\in_array((string) $type, ['void', 'null'])) {
            return 204;
        }

        $response->schema = $responseSchema = new Schema();
        $subContext = $extractionContext->createSubContext();
        $subContext->setParameter('controller-reflection-method', $source);
        $subContext->setParameter('response', $response);
        $extractionContext->getOpenApi()->extract((string) $type, $responseSchema, $subContext);

        return $subContext->getParameter('response-status-code', 200);
    }

    private function extractExceptionResponses(DocBlock $docBlock, Operation $target): void
    {
        foreach ($docBlock->getTagsByName('throws') as $throwTag) {
            if (!$throwTag instanceof DocBlock\Tags\Throws) {
                throw new \UnexpectedValueException();
            }

            $type = (string) $throwTag->getType();
            $exceptionClass = new \ReflectionClass($type);
            $exception = $exceptionClass->newInstanceWithoutConstructor();

            if (!$exception instanceof \Exception) {
                throw new \UnexpectedValueException();
            }

            [$code, $message] = $this->getExceptionInformation($exception);
            $target->responses[$code] = $exceptionResponse = new Response();

            if ((string) $throwTag->getDescription()) {
                $message = (string) $throwTag->getDescription();
            } else {
                if (!$message) {
                    $exceptionClassDocBlock = $this->createDocBlock($exceptionClass);
                    $message = $exceptionClassDocBlock->getSummary();
                }
            }

            $exceptionResponse->description = (string) $message ?: null;
        }
    }

    private function findBodyParameter(Operation $target): ?BodyParameter
    {
        foreach ($target->parameters as $parameter) {
            if ($parameter instanceof BodyParameter) {
                return $parameter;
            }
        }

        return null;
    }

    private function findParameterByName(Operation $operation, string $name): ?BaseParameter
    {
        foreach ($operation->parameters as $parameter) {
            if ($parameter->name === $name) {
                return $parameter;
            }
        }

        return null;
    }

    private function extractParameters(
        DocBlock $docBlock,
        Operation $target,
        ExtractionContextInterface $extractionContext,
    ): void {
        $bodyParameter = $this->findBodyParameter($target);

        foreach ($docBlock->getTagsByName('param') as $paramTag) {
            if (!$paramTag instanceof DocBlock\Tags\Param) {
                throw new \UnexpectedValueException();
            }

            $parameterName = trim($paramTag->getVariableName(), '$');

            $parameter = $this->findParameterByName($target, $parameterName);

            if (null !== $parameter) {
                if (!$parameter->description) {
                    $parameter->description = (string) $paramTag->getDescription() ?: null;
                }

                if ($parameter === $bodyParameter) {
                    if (!$bodyParameter->schema) {
                        $bodyParameter->schema = new Schema();
                    }

                    $subContext = $extractionContext->createSubContext();
                    $extractionContext->getOpenApi()->extract(
                        (string) $paramTag->getType(),
                        $bodyParameter->schema,
                        $subContext
                    );
                } elseif ($parameter instanceof Parameter && !$parameter->type) {
                    $primitiveType = TypeSchemaExtractor::getPrimitiveType(
                        (string) $paramTag->getType(),
                        $extractionContext
                    );

                    if (!isset($primitiveType['type'])) {
                        throw new \RuntimeException(\sprintf('No type found for parameter named [%s] for operation id [%s]', $paramTag->getVariableName(), $target->operationId));
                    }

                    $parameter->type = $primitiveType['type'];
                    if (isset($primitiveType['format'])) {
                        $parameter->format = $primitiveType['format'];
                    }
                }
                continue;
            }

            if (null !== $bodyParameter) {
                if (isset($bodyParameter->schema->properties[$parameterName])) {
                    $parameter = $bodyParameter->schema->properties[$parameterName];

                    if (!$parameter->description) {
                        $parameter->description = (string) $paramTag->getDescription() ?: null;
                    }

                    if (!$parameter->type) {
                        $subContext = $extractionContext->createSubContext();
                        $extractionContext
                            ->getOpenApi()
                            ->extract(
                                (string) $paramTag->getType(),
                                $parameter,
                                $subContext
                            );
                    }
                }
            }
        }
    }

    private function extractResponse(
        DocBlock $docBlock,
        ExtractionContextInterface $extractionContext,
        \ReflectionMethod $source,
        Operation $target,
    ): void {
        /** @var Type[] $types */
        $types = [];
        $hasVoid = false;
        $returnTag = null;
        foreach ($docBlock->getTagsByName('return') as $returnTag) {
            if (!$returnTag instanceof DocBlock\Tags\Return_) {
                throw new \UnexpectedValueException();
            }

            $type = $returnTag->getType();
            $hasVoid = $hasVoid || $type instanceof Void_;
            if ($type instanceof Compound) {
                $types = array_merge($types, $type->getIterator()->getArrayCopy());
            } else {
                $types[] = $type;
            }
        }

        if ($hasVoid && \count($types) > 1) {
            throw new \RuntimeException('Operation returning [void] cannot return anything else.');
        }

        if (!$returnTag) {
            return;
        }

        foreach ($types as $type) {
            $response = new Response();
            $response->description = (string) $returnTag->getDescription() ?: 'Operation is successful.';
            $statusCode = $this->extractStatusCode($type, $response, $extractionContext, $source);

            $target->responses[$statusCode] = $response;
        }
    }
}
