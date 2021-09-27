<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Listener;

use Draw\Bundle\OpenApiBundle\Exception\ConstraintViolationListException;
use Draw\Bundle\OpenApiBundle\Response\Listener\ApiExceptionSubscriber;
use Draw\Bundle\OpenApiBundle\Tests\TestCase;
use Exception;
use JsonSerializable;
use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

class ApiExceptionSubscriberTest extends TestCase
{
    private $httpKernel;
    private $exception;
    private $exceptionEvent;
    private $request;

    public function setUp(): void
    {
        $this->request = $this->prophesize(Request::class);
        $this->request->getRequestFormat()->willReturn('json');

        $this->httpKernel = $this->prophesize(HttpKernelInterface::class);
        $this->exception = $this->prophesize(Exception::class);

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel->reveal(),
            $this->request->reveal(),
            HttpKernelInterface::MASTER_REQUEST,
            $this->exception->reveal()
        );
    }

    public function testOnKernelExceptionNoneJsonRequest(): void
    {
        $this->request->getRequestFormat()->willReturn('html');
        $this->assertNull($this->onKernelException());
    }

    public function testOnKernelExceptionJsonResponse(): void
    {
        $this->request->getRequestFormat()->willReturn('json');
        $this->assertInstanceOf(
            Response::class,
            $response = $this->onKernelException()
        );

        $this->assertSame(
            'application/json',
            $response->headers->get('Content-Type')
        );

        $this->assertJson($response->getContent());
    }

    public function testOnKernelExceptionDefaultDebugFalse(): void
    {
        $this->assertArrayNotHasKey(
            'detail',
            json_decode($this->onKernelException()->getContent(), true)
        );
    }

    public function testOnKernelExceptionDebugFalse(): void
    {
        $this->assertArrayNotHasKey(
            'detail',
            json_decode($this->onKernelException(new ApiExceptionSubscriber(false))->getContent(), true)
        );
    }

    public function testOnKernelExceptionDebugTrue(): void
    {
        $throwable = new Exception(
            $message = 'Message',
            $code = 123
        );

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel->reveal(),
            $this->request->reveal(),
            KernelInterface::MASTER_REQUEST,
            $throwable
        );

        $responseData = json_decode($this->onKernelException(new ApiExceptionSubscriber(true))->getContent(), true);

        $this->assertArrayHasKey(
            'detail',
            $responseData
        );

        $this->assertSame(
            [
                'class' => get_class($throwable),
                'message' => $message,
                'code' => $code,
                'file' => __FILE__,
                'line' => $throwable->getLine(),
                'stack' => explode(PHP_EOL, $throwable->getTraceAsString()),
            ],
            $responseData['detail']
        );
    }

    public function testOnKernelExceptionDefaultStatusCode500(): void
    {
        $this->assertSame(
            500,
            $this->onKernelException()->getStatusCode()
        );
    }

    public function provideOnKernelException_statusCode()
    {
        yield 'ChangeDefault' => [
            new Exception(),
            [Exception::class => 400],
            400,
        ];

        yield 'FallbackOnDefault' => [
            new Exception(),
            [RuntimeException::class => 400],
            500,
        ];

        yield 'MultipleConfiguration' => [
            new Exception(),
            [RuntimeException::class => 400, Exception::class => 300],
            300,
        ];

        yield 'Extend' => [
            new OutOfBoundsException(),
            [RuntimeException::class => 400, Exception::class => 300],
            400,
        ];

        $exception = $this->prophesize(Exception::class)->willImplement(JsonSerializable::class);
        yield 'Implements' => [
            $exception->reveal(),
            [RuntimeException::class => 400, JsonSerializable::class => 300],
            300,
        ];
    }

    /**
     * @dataProvider provideOnKernelException_statusCode
     *
     * @param array<string,int> $errorCodes
     */
    public function testOnKernelExceptionErrorCode(Throwable $throwable, array $errorCodes, int $errorCode): void
    {
        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel->reveal(),
            $this->request->reveal(),
            KernelInterface::MASTER_REQUEST,
            $throwable
        );

        $this->assertSame(
            $errorCode,
            $this->onKernelException(new ApiExceptionSubscriber(false, $errorCodes))->getStatusCode()
        );
    }

    private function createConstraintListExceptionEvent(): void
    {
        $exception = new ConstraintViolationListException();
        $exception->setViolationList(
            new ConstraintViolationList([
                new ConstraintViolation('Message', null, [], null, 'test', 'invalid-value'),
            ])
        );

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel->reveal(),
            $this->request->reveal(),
            KernelInterface::MASTER_REQUEST,
            $exception
        );
    }

    public function testOnKernelExceptionDoNotIgnoreConstraintInvalidValue(): void
    {
        $this->createConstraintListExceptionEvent();

        $value = json_decode(
            $this->onKernelException(new ApiExceptionSubscriber())->getContent()
        );

        $this->assertSame('invalid-value', $value->errors[0]->invalidValue);
    }

    public function testOnKernelExceptionIgnoreConstraintInvalidValue(): void
    {
        $this->createConstraintListExceptionEvent();

        $value = json_decode(
            $this->onKernelException(new ApiExceptionSubscriber(false, [], 'errors', true))->getContent()
        );

        $this->assertFalse(isset($value->errors[0]->invalidValue));
    }

    private function onKernelException(ApiExceptionSubscriber $apiExceptionSubscriber = null): ?Response
    {
        ($apiExceptionSubscriber ?: new ApiExceptionSubscriber())->onKernelException($this->exceptionEvent);

        return $this->exceptionEvent->getResponse();
    }
}
