<?php

namespace Draw\Bundle\OpenApiBundle\Response\Listener;

use Draw\Bundle\OpenApiBundle\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var array<string,int>
     */
    private $errorCodes;

    /**
     * @var string
     */
    private $violationKey;

    /**
     * @var bool
     */
    private $ignoreConstraintInvalidValue;

    private const DEFAULT_STATUS_CODE = 500;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => ['onKernelException', 255],
            PreDumpRootSchemaEvent::class => ['addErrorDefinition'],
        ];
    }

    public function __construct(
        bool $debug = false,
        array $errorCodes = [],
        string $violationKey = 'errors',
        bool $ignoreConstraintInvalidValue = false
    ) {
        $this->debug = $debug;
        $this->errorCodes = $errorCodes;
        $this->violationKey = $violationKey;
        $this->ignoreConstraintInvalidValue = $ignoreConstraintInvalidValue;
    }

    public function addErrorDefinition(PreDumpRootSchemaEvent $event): void
    {
        $root = $event->getSchema();

        $exception = new ConstraintViolationListException();
        $code = (string) $this->getStatusCode($exception);

        $root->addDefinition('Draw.OpenApi.Error.Validation', $validationErrorSchema = new Schema());

        $validationErrorSchema->type = 'object';
        $validationErrorSchema->properties = [
            'code' => $codeSchema = new Schema(),
            'message' => $messageSchema = new Schema(),
            $this->violationKey => $violationSchema = new Schema(),
        ];

        $validationErrorSchema->required[] = 'code';
        $codeSchema->type = 'integer';
        $messageSchema->type = 'string';
        $violationSchema->type = 'object';
        $violationSchema->properties = [
            'propertyPath' => $propertyPath = new Schema(),
            'message' => $messageSchema = new Schema(),
            'invalidValue' => $invalidValueSchema = new Schema(),
            'code' => $codeSchema = new Schema(),
            'payload' => $payloadSchema = new Schema(),
        ];

        $propertyPath->type = 'string';
        $invalidValueSchema->type = 'mixed';
        $messageSchema->type = 'string';
        $codeSchema->type = 'string';
        $payloadSchema->type = 'mixed';

        foreach ($root->paths as $pathItem) {
            foreach ($pathItem->getOperations() as $operation) {
                if (isset($operation->responses[$code])) {
                    continue;
                }
                $operation->responses[$code] = $response = new Response();
                $response->description = 'Request Validation error';
                $responseSchema = new Schema();
                $response->schema = $responseSchema;
                $responseSchema->ref = $root->getDefinitionReference('Draw.OpenApi.Error.Validation');
            }
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if ('json' !== $request->getRequestFormat()) {
            return;
        }

        $error = $event->getThrowable();
        $statusCode = $this->getStatusCode($error);

        $data = [
            'code' => $statusCode,
            'message' => $error->getMessage(),
        ];

        if ($error instanceof ConstraintViolationListException) {
            $data[$this->violationKey] = $this->getConstraintViolationData($error);
        }

        if ($this->debug) {
            $data['detail'] = $this->getExceptionDetail($error);
        }

        $event->setResponse(
            new JsonResponse(
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
                $statusCode,
                [],
                true
            )
        );
    }

    private function getConstraintViolationData(ConstraintViolationListException $exception): array
    {
        $errors = [];
        foreach ($exception->getViolationList() as $constraintViolation) {
            /* @var $constraintViolation ConstraintViolationInterface */
            $errorData = [
                'propertyPath' => $constraintViolation->getPropertyPath(),
                'message' => $constraintViolation->getMessage(),
                'invalidValue' => $constraintViolation->getInvalidValue(),
                'code' => $constraintViolation->getCode(),
            ];

            if ($this->ignoreConstraintInvalidValue) {
                unset($errorData['invalidValue']);
            }

            switch (true) {
                case !($constraint = $constraintViolation->getConstraint()):
                case null === $constraint->payload:
                    break;
                default:
                    $errorData['payload'] = $constraint->payload;
                    break;
            }

            $errors[] = $errorData;
        }

        return $errors;
    }

    private function getExceptionDetail(Throwable $e): array
    {
        $result = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($this->debug) {
            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                $result['stack'][] = $line;
            }

            if ($previous = $e->getPrevious()) {
                $result['previous'] = $this->getExceptionDetail($previous);
            }
        }

        return $result;
    }

    /**
     * @param $exception
     */
    private function getStatusCode($exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return $this->getStatusCodeFromErrorCodes($exception) ?: self::DEFAULT_STATUS_CODE;
    }

    private function getStatusCodeFromErrorCodes(Throwable $exception): ?int
    {
        $exceptionClass = get_class($exception);
        $reflectionExceptionClass = new ReflectionClass($exceptionClass);

        foreach ($this->errorCodes as $exceptionMapClass => $value) {
            if (!$value) {
                continue;
            }

            switch (true) {
                case $exceptionClass === $exceptionMapClass:
                case $reflectionExceptionClass->isSubclassOf($exceptionMapClass):
                    return $value;
            }
        }

        return false;
    }
}
