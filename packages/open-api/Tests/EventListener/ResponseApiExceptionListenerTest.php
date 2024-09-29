<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Schema\Operation;
use Draw\Component\OpenApi\Schema\PathItem;
use Draw\Component\OpenApi\Schema\Response as OpenResponse;
use Draw\Component\OpenApi\Schema\Root;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

#[CoversClass(ResponseApiExceptionListener::class)]
class ResponseApiExceptionListenerTest extends TestCase
{
    private ResponseApiExceptionListener $object;

    private HttpKernelInterface $httpKernel;
    private \Exception $exception;
    private ExceptionEvent $exceptionEvent;
    private Request $request;

    protected function setUp(): void
    {
        $this->object = new ResponseApiExceptionListener();

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel = $this->createMock(HttpKernelInterface::class),
            $this->request = $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            $this->exception = new \Exception(
                previous: new \Exception()
            )
        );

        $this->request
            ->expects(static::any())
            ->method('getRequestFormat')
            ->willReturn('json');
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
        $exceptionDetail = json_decode(
            $this->onKernelException()->getContent(),
            true,
            512,
            \JSON_THROW_ON_ERROR
        )['detail'];

        static::assertArrayNotHasKey(
            'stack',
            $exceptionDetail
        );
    }

    public function testOnKernelExceptionDebugFalse(): void
    {
        $exceptionDetail = json_decode(
            $this->onKernelException(
                new ResponseApiExceptionListener(debug: false)
            )->getContent(),
            true,
            512,
            \JSON_THROW_ON_ERROR
        )['detail'];

        $expectedKeys = ['class', 'message', 'code', 'file', 'line', 'previous'];

        static::assertEmpty(
            $extraKeys = array_diff(array_keys($exceptionDetail), $expectedKeys),
            \sprintf(
                'Unexpected keys: %s',
                implode(', ', $extraKeys)
            )
        );

        static::assertEmpty(
            $missingKeys = array_diff($expectedKeys, array_keys($exceptionDetail)),
            \sprintf(
                'Missing keys: %s',
                implode(', ', $missingKeys)
            )
        );
    }

    public function testOnKernelExceptionDebugTrue(): void
    {
        $throwable = new \Exception(
            uniqid('message-'),
            random_int(\PHP_INT_MIN, \PHP_INT_MAX),
            $previous = new \Exception()
        );

        $this->exceptionEvent = new ExceptionEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $throwable
        );

        $responseData = json_decode(
            $this->onKernelException(new ResponseApiExceptionListener(debug: true))->getContent(),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        static::assertArrayHasKey(
            'detail',
            $responseData
        );

        static::assertSame(
            [
                'class' => $throwable::class,
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'file' => __FILE__,
                'line' => $throwable->getLine(),
                'stack' => explode(\PHP_EOL, $throwable->getTraceAsString()),
                'previous' => [
                    'class' => $previous::class,
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

    public function testOnKernelExceptionPayload(): void
    {
        $this->createConstraintListExceptionEvent($constraint = new NotNull(['payload' => uniqid('payload-')]));

        $value = json_decode(
            $this->onKernelException(new ResponseApiExceptionListener())->getContent(),
            null,
            512,
            \JSON_THROW_ON_ERROR
        );

        static::assertSame(
            $constraint->payload,
            $value->errors[0]->payload
        );
    }

    private function onKernelException(?ResponseApiExceptionListener $apiExceptionSubscriber = null): ?Response
    {
        ($apiExceptionSubscriber ?: $this->object)->onKernelException($this->exceptionEvent);

        return $this->exceptionEvent->getResponse();
    }
}
