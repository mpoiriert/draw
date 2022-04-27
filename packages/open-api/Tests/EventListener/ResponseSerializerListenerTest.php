<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use Draw\Component\OpenApi\EventListener\ResponseSerializerListener;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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

    private SerializerInterface $serializer;

    private SerializationContextFactoryInterface $serializationContextFactory;

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
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
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
            ->expects($this->never())
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
            ->expects($this->never())
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
            ->expects($this->never())
            ->method('createSerializationContext');

        $this->object->onKernelView($event);

        $response = $event->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
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
            ->expects($this->once())
            ->method('createSerializationContext')
            ->willReturn($context = new SerializationContext());

        $request->attributes->set('_draw_open_api_serialization', $serialization = new Serialization([]));

        $serialization->setSerializerVersion($version = uniqid('version-'));
        $serialization->setSerializerGroups($groups = [uniqid('group-')]);
        $serialization->setContextAttributes(['key' => 'value']);
        $serialization->setStatusCode(201);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (PreSerializerResponseEvent $event) use (
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
            ->expects($this->once())
            ->method('serialize')
            ->with($result, 'json', $context)
            ->willReturn($jsonResult = json_encode(['key' => uniqid('value-')]));

        $this->object->onKernelView($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertSame(
            'application/json',
            $response->headers->get('Content-Type')
        );

        $this->assertSame($jsonResult, $response->getContent());
        $this->assertSame($serialization->getStatusCode(), $response->getStatusCode());
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
            ->expects($this->once())
            ->method('add')
            ->with($headers);

        $headerBag
            ->expects($this->once())
            ->method('allPreserveCase')
            ->willReturn($headers);

        $this->object->onKernelResponse($responseEvent);
    }

    public function testSetResponseHeaderInvalidResponseHeaderBag(): void
    {
        $request = new Request();

        $request->attributes->set('_responseHeaderBag', (object) []);

        $this->expectException(RuntimeException::class);
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
            ->expects($this->once())
            ->method('set')
            ->with(
                $key = uniqid('key-'),
                $values = ['values'],
                false
            );

        $this->object::setResponseHeader($request, $key, $values, false);
    }
}
