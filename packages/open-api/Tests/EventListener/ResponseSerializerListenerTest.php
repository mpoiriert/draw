<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use Draw\Component\OpenApi\EventListener\ResponseSerializerListener;
use Draw\Component\OpenApi\Serializer\Serialization;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Draw\Component\OpenApi\EventListener\ResponseSerializerListener
 */
class ResponseSerializerListenerTest extends TestCase
{
    private ResponseSerializerListener $object;

    /**
     * @var SerializerInterface&MockObject
     */
    private SerializerInterface $serializer;

    /**
     * @var SerializationContextFactoryInterface&MockObject
     */
    private SerializationContextFactoryInterface $serializationContextFactory;

    /**
     * @var EventDispatcherInterface&MockObject
     */
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->object = new ResponseSerializerListener(
            $this->serializer = $this->createMock(SerializerInterface::class),
            $this->serializationContextFactory = $this->createMock(SerializationContextFactoryInterface::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            false
        );
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
                KernelEvents::VIEW => ['onKernelView', 30],
                KernelEvents::RESPONSE => ['onKernelResponse', 30],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testOnKernelViewAlreadyResponse(): void
    {
        $event = new ViewEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );

        $this->serializationContextFactory
            ->expects(static::never())
            ->method('createSerializationContext');

        $this->object->onKernelView($event);
    }

    public function testOnKernelViewRequestNotJson(): void
    {
        $event = new ViewEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            null
        );

        $request->setRequestFormat('html');

        $this->serializationContextFactory
            ->expects(static::never())
            ->method('createSerializationContext');

        $this->object->onKernelView($event);
    }

    public function testOnKernelViewResponseNull(): void
    {
        $event = new ViewEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            null
        );

        $request->setRequestFormat('json');

        $this->serializationContextFactory
            ->expects(static::never())
            ->method('createSerializationContext');

        $this->object->onKernelView($event);

        $response = $event->getResponse();

        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testOnKernelView(): void
    {
        $result = (object) [];
        $event = new ViewEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $result
        );

        $request->setRequestFormat('json');

        $this->serializationContextFactory
            ->expects(static::once())
            ->method('createSerializationContext')
            ->willReturn($context = new SerializationContext());

        $request->attributes->set(
            '_draw_open_api_serialization',
            $serialization = new Serialization(
                statusCode: 201,
                serializerGroups: $groups = [uniqid('group-')],
                serializerVersion: $version = uniqid('version-'),
                contextAttributes: ['key' => 'value']
            )
        );

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(
                    function (PreSerializerResponseEvent $event) use (
                        $context,
                        $result,
                        $serialization,
                        $version,
                        $groups
                    ) {
                        $this->assertSame($result, $event->getResult());

                        $this->assertSame($serialization, $event->getSerialization());

                        $this->assertSame(
                            $context,
                            $event->getContext()
                        );

                        $this->assertSame(
                            $version,
                            $context->getAttribute('version')
                        );

                        $this->assertSame(
                            $groups,
                            $context->getAttribute('groups')
                        );

                        $this->assertSame(
                            'value',
                            $context->getAttribute('key')
                        );

                        return true;
                    }
                )
            );

        $this->serializer
            ->expects(static::once())
            ->method('serialize')
            ->with($result, 'json', $context)
            ->willReturn($jsonResult = json_encode(['key' => uniqid('value-')], \JSON_THROW_ON_ERROR));

        $this->object->onKernelView($event);

        $response = $event->getResponse();

        static::assertInstanceOf(JsonResponse::class, $response);

        static::assertSame(
            'application/json',
            $response->headers->get('Content-Type')
        );

        static::assertSame($jsonResult, $response->getContent());
        static::assertSame($serialization->statusCode, $response->getStatusCode());
    }

    public function testOnKernelResponse(): void
    {
        $responseEvent = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $response = new Response()
        );

        $response->headers = $responseHeaderBag = $this->createMock(ResponseHeaderBag::class);

        $request->attributes->set(
            '_responseHeaderBag',
            $headerBag = $this->createMock(ResponseHeaderBag::class)
        );

        $headers = ['key' => 'value'];

        $responseHeaderBag
            ->expects(static::once())
            ->method('add')
            ->with($headers);

        $headerBag
            ->expects(static::once())
            ->method('allPreserveCase')
            ->willReturn($headers);

        $this->object->onKernelResponse($responseEvent);
    }

    public function testSetResponseHeaderInvalidResponseHeaderBag(): void
    {
        $request = new Request();

        $request->attributes->set('_responseHeaderBag', (object) []);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The current attribute value of [_responseHeaderBag] is invalid');

        $this->object::setResponseHeader($request, uniqid('key-'), ['values']);
    }

    public function testSetResponseHeader(): void
    {
        $request = new Request();

        $request->attributes->set(
            '_responseHeaderBag',
            $responseHeaderBag = $this->createMock(ResponseHeaderBag::class)
        );

        $responseHeaderBag
            ->expects(static::once())
            ->method('set')
            ->with(
                $key = uniqid('key-'),
                $values = ['values'],
                false
            );

        $this->object::setResponseHeader($request, $key, $values, false);
    }
}
