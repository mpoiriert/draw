<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Response as OpenResponse;
use Draw\Component\OpenApi\Schema\Root;
use Exception;
use JsonSerializable;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

/**
 * @covers \Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener
 */
class ResponseApiExceptionListenerTest extends TestCase
{
    private ResponseApiExceptionListener $object;
    private HttpKernelInterface $httpKernel;
    private Exception $exception;
    private ExceptionEvent $exceptionEvent;
    private Request $request;

    protected function setUp(): void
    {
        $this->object = new ResponseApiExceptionListener();

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel = $this->createMock(HttpKernelInterface::class),
            $this->request = $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            $this->exception = $this->createMock(Exception::class)
        );

        $this->request
            ->expects(static::any())
            ->method('getRequestFormat')
            ->willReturn('json');
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testSubscribedEvents(): void
    {
        static::assertSame(
            [
                ExceptionEvent::class => ['onKernelException', 255],
                PreDumpRootSchemaEvent::class => ['addErrorDefinition'],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    /**
     * todo Improve test asser on this.
     */
    public function testAddErrorDefinition(): void
    {
        $root = new Root();

        $root->paths['/not-set'] = $notSetPathItem = new PathItem();
        $notSetPathItem->get = new Operation();

        $root->paths['/already-set'] = $alreadySetPathItem = new PathItem();
        $alreadySetPathItem->get = new Operation();
        $alreadySetPathItem->get->responses['500'] = $exitingSchema = new OpenResponse();

        $this->object->addErrorDefinition(
            $event = new PreDumpRootSchemaEvent($root)
        );

        static::assertArrayHasKey(
            'Draw.OpenApi.Error.Validation',
            $event->getSchema()->definitions
        );

        static::assertSame(
            $exitingSchema,
            $alreadySetPathItem->get->responses['500']
        );

        static::assertInstanceOf(
            OpenResponse::class,
            $notSetPathItem->get->responses['500']
        );
    }

    public function testOnKernelExceptionNoneJsonRequest(): void
    {
        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel,
            $this->request = $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            $this->exception
        );

        $this->request
            ->expects(static::any())
            ->method('getRequestFormat')
            ->willReturn('html');

        static::assertNull($this->onKernelException());
    }

    public function testOnKernelExceptionJsonResponse(): void
    {
        static::assertInstanceOf(
            Response::class,
            $response = $this->onKernelException()
        );

        static::assertSame(
            'application/json',
            $response->headers->get('Content-Type')
        );

        static::assertJson($response->getContent());
    }

    public function testOnKernelExceptionDefaultDebugFalse(): void
    {
        static::assertArrayNotHasKey(
            'detail',
            json_decode($this->onKernelException()->getContent(), true)
        );
    }

    public function testOnKernelExceptionDebugFalse(): void
    {
        static::assertArrayNotHasKey(
            'detail',
            json_decode($this->onKernelException(new ResponseApiExceptionListener(false))->getContent(), true)
        );
    }

    public function testOnKernelExceptionDebugTrue(): void
    {
        $throwable = new Exception(
            uniqid('message-'),
            rand(\PHP_INT_MIN, \PHP_INT_MAX),
            $previous = new Exception()
        );

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $throwable
        );

        $responseData = json_decode(
            $this->onKernelException(new ResponseApiExceptionListener(true))->getContent(),
            true
        );

        static::assertArrayHasKey(
            'detail',
            $responseData
        );

        static::assertSame(
            [
                'class' => \get_class($throwable),
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'file' => __FILE__,
                'line' => $throwable->getLine(),
                'stack' => explode(\PHP_EOL, $throwable->getTraceAsString()),
                'previous' => [
                    'class' => \get_class($previous),
                    'message' => $previous->getMessage(),
                    'code' => $previous->getCode(),
                    'file' => $previous->getFile(),
                    'line' => $previous->getLine(),
                    'stack' => explode(\PHP_EOL, $previous->getTraceAsString()),
                ],
            ],
            $responseData['detail']
        );
    }

    public function testOnKernelExceptionDefaultStatusCode500(): void
    {
        static::assertSame(
            500,
            $this->onKernelException()->getStatusCode()
        );
    }

    public function provideOnKernelExceptionStatusCode(): iterable
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

        $exception = new class() extends Exception implements JsonSerializable {
            public function jsonSerialize()
            {
            }
        };

        yield 'Implements' => [
            $exception,
            [RuntimeException::class => 400, JsonSerializable::class => 300],
            300,
        ];
    }

    /**
     * @dataProvider provideOnKernelExceptionStatusCode
     *
     * @param array<string,int> $errorCodes
     */
    public function testOnKernelExceptionErrorCode(Throwable $throwable, array $errorCodes, int $errorCode): void
    {
        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $throwable
        );

        static::assertSame(
            $errorCode,
            $this->onKernelException(new ResponseApiExceptionListener(false, $errorCodes))->getStatusCode()
        );
    }

    private function createConstraintListExceptionEvent(?Constraint $constraint = null): void
    {
        $exception = new ConstraintViolationListException(
            new ConstraintViolationList([
                new ConstraintViolation(
                    'Message',
                    null,
                    [],
                    null,
                    'test',
                    'invalid-value',
                    null,
                    null,
                    $constraint
                ),
            ])
        );

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );
    }

    public function testOnKernelExceptionDoNotIgnoreConstraintInvalidValue(): void
    {
        $this->createConstraintListExceptionEvent();

        $value = json_decode(
            $this->onKernelException(new ResponseApiExceptionListener())->getContent()
        );

        static::assertSame('invalid-value', $value->errors[0]->invalidValue);
    }

    public function testOnKernelExceptionIgnoreConstraintInvalidValue(): void
    {
        $this->createConstraintListExceptionEvent();

        $value = json_decode(
            $this->onKernelException(new ResponseApiExceptionListener(false, [], 'errors', true))->getContent()
        );

        static::assertFalse(isset($value->errors[0]->invalidValue));
    }

    public function testOnKernelExceptionPayload(): void
    {
        $this->createConstraintListExceptionEvent($constraint = new NotNull(['payload' => uniqid('payload-')]));

        $value = json_decode(
            $this->onKernelException(new ResponseApiExceptionListener(false, [], 'errors', true))->getContent()
        );

        static::assertSame(
            $constraint->payload,
            $value->errors[0]->payload
        );
    }

    private function onKernelException(ResponseApiExceptionListener $apiExceptionSubscriber = null): ?Response
    {
        ($apiExceptionSubscriber ?: $this->object)->onKernelException($this->exceptionEvent);

        return $this->exceptionEvent->getResponse();
    }
}
