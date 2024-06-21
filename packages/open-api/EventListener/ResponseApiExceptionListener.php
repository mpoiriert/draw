<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter\ConfigurableErrorToHttpCodeConverter;
use Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter\ErrorToHttpCodeConverterInterface;
use Draw\Component\OpenApi\Schema\Response;
use Draw\Component\OpenApi\Schema\Schema;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

final class ResponseApiExceptionListener
{
    public function __construct(
        /**
         * @var iterable<ErrorToHttpCodeConverterInterface>
         */
        #[TaggedIterator(ErrorToHttpCodeConverterInterface::class)]
        private iterable $errorToHttpCodeConverters = [],
        private bool $debug = false,
        private string $violationKey = 'errors',
    ) {
        $this->errorToHttpCodeConverters ??= new ConfigurableErrorToHttpCodeConverter();
    }

    #[AsEventListener]
    public function addErrorDefinition(PreDumpRootSchemaEvent $event): void
    {
        $root = $event->getSchema();

        if (!$root->paths) {
            return;
        }

        $exception = new ConstraintViolationListException(new ConstraintViolationList());
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
            'code' => $codeSchema = new Schema(),
            'payload' => $payloadSchema = new Schema(),
        ];

        $propertyPath->type = 'string';
        $messageSchema->type = 'string';
        $codeSchema->type = 'string';
        $payloadSchema->type = 'object';

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

    #[AsEventListener(priority: 255)]
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

        $data['detail'] = $this->getExceptionDetail($error);

        $event->setResponse(
            new JsonResponse(
                json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION),
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
                'code' => $constraintViolation->getCode(),
            ];

            if (null !== $payload = $this->getConstraintPayload($constraintViolation)) {
                $errorData['payload'] = $payload;
            }

            $errors[] = $errorData;
        }

        return $errors;
    }

    private function getConstraintPayload(ConstraintViolationInterface $constraintViolation): mixed
    {
        if (!$constraintViolation instanceof ConstraintViolation) {
            return null;
        }

        if (!$constraint = $constraintViolation->getConstraint()) {
            return null;
        }

        return $constraint->payload;
    }

    private function getExceptionDetail(\Throwable $e): array
    {
        $result = [
            'class' => $e::class,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($this->debug) {
            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                $result['stack'][] = $line;
            }
        }

        if ($previous = $e->getPrevious()) {
            $result['previous'] = $this->getExceptionDetail($previous);
        }

        return $result;
    }

    private function getStatusCode(\Throwable $error): int
    {
        foreach ($this->errorToHttpCodeConverters as $converter) {
            if (null !== $statusCode = $converter->convertToHttpCode($error)) {
                return $statusCode;
            }
        }

        return 500;
    }
}
