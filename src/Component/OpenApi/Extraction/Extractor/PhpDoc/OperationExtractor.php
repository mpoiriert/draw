<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\PhpDoc;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\BodyParameter;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Void_;
use ReflectionClass;
use ReflectionMethod;
use Reflector;
use RuntimeException;

class OperationExtractor implements ExtractorInterface
{
    private $contextFactory;
    private $docBlockFactory;
    private $exceptionResponseCodes = [];

    public function __construct(
        DocBlockFactoryInterface $docBlockFactory = null
    ) {
        $this->contextFactory = new ContextFactory();
        $this->docBlockFactory = $docBlockFactory ?: DocBlockFactory::createInstance();
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     *
     * @return bool
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
            return false;
        }

        return true;
    }

    /**
     * @return DocBlock
     */
    private function createDocBlock(Reflector $reflector)
    {
        return $this->docBlockFactory
            ->create(
                $reflector,
                $this->contextFactory->createFromReflector($reflector)
            );
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionMethod $method
     * @param Operation        $operation
     */
    public function extract($method, $operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        try {
            $docBlock = $this->createDocBlock($method);
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        if (!$operation->summary) {
            $operation->summary = (string) $docBlock->getSummary() ?: null;
        }

        if (!$operation->description) {
            $operation->description = (string) $docBlock->getDescription() ?: null;
        }

        /** @var Type[] $types */
        $types = [];
        $hasVoid = false;
        $returnTag = null;
        foreach ($docBlock->getTagsByName('return') as $returnTag) {
            /* @var $returnTag DocBlock\Tags\Return_ */
            $type = $returnTag->getType();
            $hasVoid = $hasVoid || $type instanceof Void_;
            if ($type instanceof Compound) {
                $types = array_merge($types, $type->getIterator()->getArrayCopy());
            } else {
                $types[] = $type;
            }
        }

        if ($hasVoid && count($types) > 1) {
            throw new RuntimeException('Operation returning [void] cannot return anything else.');
        }

        if ($returnTag) {
            foreach ($types as $type) {
                $response = new Response();
                $response->description = (string) $returnTag->getDescription() ?: null;
                if ('void' != $type && 'null' != $type) {
                    $response->schema = $responseSchema = new Schema();
                    $subContext = $extractionContext->createSubContext();
                    $subContext->setParameter('controller-reflection-method', $method);
                    $subContext->setParameter('response', $response);
                    $extractionContext->getOpenApi()->extract((string) $type, $responseSchema, $subContext);
                    $statusCode = $subContext->getParameter('response-status-code', 200);
                } else {
                    $statusCode = 204;
                }

                $operation->responses[$statusCode] = $response;
            }
        }

        if ($docBlock->getTagsByName('deprecated')) {
            $operation->deprecated = true;
        }

        foreach ($docBlock->getTagsByName('throws') as $throwTag) {
            /* @var $throwTag DocBlock\Tags\Throws */

            $type = (string) $throwTag->getType();
            $exceptionClass = new ReflectionClass((string) $type);
            /** @var Exception $exception */
            $exception = $exceptionClass->newInstanceWithoutConstructor();
            list($code, $message) = $this->getExceptionInformation($exception);
            $operation->responses[$code] = $exceptionResponse = new Response();

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

        $bodyParameter = null;

        foreach ($operation->parameters as $parameter) {
            if ($parameter instanceof BodyParameter) {
                $bodyParameter = $parameter;
                break;
            }
        }

        foreach ($docBlock->getTagsByName('param') as $paramTag) {
            /* @var $paramTag DocBlock\Tags\Param */

            $parameterName = trim($paramTag->getVariableName(), '$');

            $parameter = null;
            foreach ($operation->parameters as $existingParameter) {
                if ($existingParameter->name == $parameterName) {
                    $parameter = $existingParameter;
                    break;
                }
            }

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
                } elseif (!$parameter->type) {
                    $parameter->type = TypeSchemaExtractor::getPrimitiveType(
                        (string) $paramTag->getType(),
                        $extractionContext
                    )['type'];
                }
                continue;
            }

            if (null !== $bodyParameter) {
                /* @var BodyParameter $bodyParameter */
                if (isset($bodyParameter->schema->properties[$parameterName])) {
                    $parameter = $bodyParameter->schema->properties[$parameterName];

                    if (!$parameter->description) {
                        $parameter->description = (string) $paramTag->getDescription() ?: null;
                    }

                    if (!$parameter->type) {
                        $subContext = $extractionContext->createSubContext();
                        $extractionContext->getOpenApi()->extract((string) $paramTag->getType(), $parameter, $subContext);
                    }

                    continue;
                }
            }
        }
    }

    private function getExceptionInformation(Exception $exception)
    {
        foreach ($this->exceptionResponseCodes as $class => $information) {
            if ($exception instanceof $class) {
                return $information;
            }
        }

        return [500, null];
    }

    public function registerExceptionResponseCodes($exceptionClass, $code = 500, $message = null)
    {
        $this->exceptionResponseCodes[$exceptionClass] = [$code, $message];
    }
}
