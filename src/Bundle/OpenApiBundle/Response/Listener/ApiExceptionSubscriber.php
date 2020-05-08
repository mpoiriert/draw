<?php namespace Draw\Bundle\OpenApiBundle\Response\Listener;

use Draw\Bundle\OpenApiBundle\Exception\ConstraintViolationListException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $debug;

    private $exceptionCodes;

    /**
     * @var string
     */
    private $violationKey;

    const DEFAULT_STATUS_CODE = 500;

    public function __construct(
        $debug = false,
        $exceptionCodes = [],
        $violationKey = 'errors'
    ) {
        $this->debug = $debug;
        $this->exceptionCodes = $exceptionCodes;
        $this->violationKey = $violationKey;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ExceptionEvent::class => ['onKernelException', 255]
        ];
    }

    /**
     * @param $exception
     * @return int
     */
    protected function getStatusCode($exception)
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        return $this->isSubclassOf($exception, $this->exceptionCodes) ?: self::DEFAULT_STATUS_CODE;
    }

    /**
     * @param $exception
     * @param $exceptionMap
     * @return bool|string
     */
    protected function isSubclassOf($exception, $exceptionMap)
    {
        $exceptionClass = get_class($exception);
        $reflectionExceptionClass = new ReflectionClass($exceptionClass);

        foreach ($exceptionMap as $exceptionMapClass => $value) {
            if ($value
                && ($exceptionClass === $exceptionMapClass || $reflectionExceptionClass->isSubclassOf($exceptionMapClass))
            ) {
                return $value;
            }
        }

        return false;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $error = $event->getThrowable();
        $request = $event->getRequest();

        if ($request->getRequestFormat() !== 'json') {
            return;
        }

        $this->logger->notice('Intercepted error', $this->getExceptionDetail($error, false));

        $statusCode = $this->getStatusCode($error);

        $data = [
            "code" => $statusCode,
            "message" => $error->getMessage()
        ];

        if ($error instanceof ConstraintViolationListException) {
            $errors = [];
            foreach ($error->getViolationList() as $constraintViolation) {
                /* @var $constraintViolation ConstraintViolationInterface */
                $errorData = [
                    'propertyPath' => $constraintViolation->getPropertyPath(),
                    'message' => $constraintViolation->getMessage(),
                    'invalidValue' => $constraintViolation->getInvalidValue(),
                    'code' => $constraintViolation->getCode()
                ];

                if ($constraintViolation->getConstraint() && null !== ($payload = $constraintViolation->getConstraint()->payload)) {
                    $errorData['payload'] = $payload;
                }

                $errors[] = $errorData;
            }

            $data[$this->violationKey] = $errors;
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

    public function getExceptionDetail(\Throwable $e, $full = true)
    {
        $result = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];

        if ($full) {
            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                $result['stack'][] = $line;
            }

            if ($previous = $e->getPrevious()) {
                $result['previous'] = $this->getExceptionDetail($previous);
            }
        }


        return $result;
    }
}