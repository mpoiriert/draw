<?php namespace Draw\Bundle\OpenApiBundle\Response\Listener;

use Draw\Bundle\OpenApiBundle\Exception\ConstraintViolationListException;
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

    private const DEFAULT_STATUS_CODE = 500;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => ['onKernelException', 255]
        ];
    }

    public function __construct(
        bool $debug = false,
        array $errorCodes = [],
        string $violationKey = 'errors'
    ) {
        $this->debug = $debug;
        $this->errorCodes = $errorCodes;
        $this->violationKey = $violationKey;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getRequestFormat() !== 'json') {
            return;
        }

        $error = $event->getThrowable();
        $statusCode = $this->getStatusCode($error);

        $data = [
            "code" => $statusCode,
            "message" => $error->getMessage()
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
                'code' => $constraintViolation->getCode()
            ];

            switch (true) {
                case !($constraint = $constraintViolation->getConstraint()):
                case $constraint->payload === null:
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
            'line' => $e->getLine()
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
     * @return int
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
            if(!$value) {
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