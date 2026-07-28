<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use Draw\Component\OpenApi\EventListener\ResponseSerializerListener;
use Draw\Component\OpenApi\Serializer\Serialization;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
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
 * @internal
 */
#[CoversClass(ResponseSerializerListener::class)]
class ResponseSerializerListenerTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $this->createStub(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $this->assertSame(
            [
                KernelEvents::VIEW => ['onKernelView', 30],
                KernelEvents::RESPONSE => ['onKernelResponse', 30],
            ],
            $object::getSubscribedEvents()
        );
    }

    public function testOnKernelViewAlreadyResponse(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $serializationContextFactory = $this->createMock(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $event = new ViewEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );

        $serializationContextFactory
            ->expects($this->never())
            ->method('createSerializationContext')
        ;

        $object->onKernelView($event);
    }

    public function testOnKernelViewRequestNotJson(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $serializationContextFactory = $this->createMock(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $event = new ViewEvent(
            $this->createStub(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            null
        );

        $request->setRequestFormat('html');

        $serializationContextFactory
            ->expects($this->never())
            ->method('createSerializationContext')
        ;

        $object->onKernelView($event);
    }

    public function testOnKernelViewResponseNull(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $serializationContextFactory = $this->createMock(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $event = new ViewEvent(
            $this->createStub(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            null
        );

        $request->setRequestFormat('json');

        $serializationContextFactory
            ->expects($this->never())
            ->method('createSerializationContext')
        ;

        $object->onKernelView($event);

        $response = $event->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testOnKernelView(): void
    {
        $object = new ResponseSerializerListener(
            $serializer = $this->createMock(SerializerInterface::class),
            $serializationContextFactory = $this->createMock(SerializationContextFactoryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            false
        );

        $result = (object) [];
        $event = new ViewEvent(
            $this->createStub(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $result
        );

        $request->setRequestFormat('json');

        $serializationContextFactory
            ->expects($this->once())
            ->method('createSerializationContext')
            ->willReturn($context = new SerializationContext())
        ;

        $request->attributes->set(
            '_draw_open_api_serialization',
            $serialization = new Serialization(
                statusCode: 201,
                serializerGroups: $groups = [uniqid('group-')],
                serializerVersion: $version = uniqid('version-'),
                contextAttributes: ['key' => 'value']
            )
        );

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
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
            )
        ;

        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($result, 'json', $context)
            ->willReturn($jsonResult = json_encode(['key' => uniqid('value-')], \JSON_THROW_ON_ERROR))
        ;

        $object->onKernelView($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertSame(
            'application/json',
            $response->headers->get('Content-Type')
        );

        $this->assertSame($jsonResult, $response->getContent());
        $this->assertSame($serialization->statusCode, $response->getStatusCode());
    }

    public function testOnKernelResponse(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $this->createStub(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $responseEvent = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
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
            ->expects($this->once())
            ->method('add')
            ->with($headers)
        ;

        $headerBag
            ->expects($this->once())
            ->method('allPreserveCase')
            ->willReturn($headers)
        ;

        $object->onKernelResponse($responseEvent);
    }

    public function testSetResponseHeaderInvalidResponseHeaderBag(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $this->createStub(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $request = new Request();

        $request->attributes->set('_responseHeaderBag', (object) []);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The current attribute value of [_responseHeaderBag] is invalid');

        $object::setResponseHeader($request, uniqid('key-'), ['values']);
    }

    public function testSetResponseHeader(): void
    {
        $object = new ResponseSerializerListener(
            $this->createStub(SerializerInterface::class),
            $this->createStub(SerializationContextFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            false
        );

        $request = new Request();

        $request->attributes->set(
            '_responseHeaderBag',
            $responseHeaderBag = $this->createMock(ResponseHeaderBag::class)
        );

        $responseHeaderBag
            ->expects($this->once())
            ->method('set')
            ->with(
                $key = uniqid('key-'),
                $values = ['values'],
                false
            )
        ;

        $object::setResponseHeader($request, $key, $values, false);
    }
}
