<?php

namespace Draw\Component\Messenger\Tests\Controller;

use DateTimeImmutable;
use Draw\Component\Messenger\Controller\MessageController;
use Draw\Component\Messenger\EnvelopeFinder;
use Draw\Component\Messenger\Event\MessageLinkErrorEvent;
use Draw\Component\Messenger\Exception\MessageExpiredException;
use Draw\Component\Messenger\Exception\MessageNotFoundException;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Stamp\FindFromTransportStamp;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \Draw\Component\Messenger\Controller\MessageController
 */
class MessageControllerTest extends TestCase
{
    private MessageController $controller;

    private MessageBusInterface $messageBus;

    private EnvelopeFinder $envelopeFinder;

    private EventDispatcherInterface $eventDispatcher;

    private TranslatorInterface $translator;

    private ContainerInterface $transportLocator;

    private Request $request;

    public function setUp(): void
    {
        $this->request = new Request();
        $this->request->setSession(
            new Session(
                new MockArraySessionStorage(),
            )
        );

        $this->controller = new MessageController(
            $this->messageBus = $this->createMock(MessageBusInterface::class),
            $this->envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $this->translator = $this->createMock(TranslatorInterface::class),
            $this->transportLocator = $this->createMock(ContainerInterface::class)
        );
    }

    public function testConstants(): void
    {
        $this->assertSame(
            'dMUuid',
            $this->controller::MESSAGE_ID_PARAMETER_NAME
        );
    }

    public function provideTestClickEnvelopeError(): iterable
    {
        yield 'not-found' => [
            null,
            MessageNotFoundException::class,
            'link.invalid',
        ];

        yield 'error-queue' => [
            new Envelope((object) [], [new SentToFailureTransportStamp(uniqid())]),
            MessageNotFoundException::class,
            'link.invalid',
        ];

        yield 'expired' => [
            new Envelope((object) [], [new ExpirationStamp(new DateTimeImmutable('- 1 hours'))]),
            MessageExpiredException::class,
            null,
        ];
    }

    /**
     * @dataProvider provideTestClickEnvelopeError
     */
    public function testClickEnvelopeError(
        ?Envelope $returnedEnveloped,
        string $exceptionClass,
        ?string $translatedMessage
    ): void {
        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn($returnedEnveloped);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
                    function (MessageLinkErrorEvent $event) use ($messageId, $exceptionClass) {
                        $this->assertSame(
                            $this->request,
                            $event->getRequest()
                        );

                        $this->assertSame(
                            $messageId,
                            $event->getMessageId()
                        );

                        $error = $event->getError();

                        $this->assertInstanceOf(
                            $exceptionClass,
                            $error
                        );

                        return true;
                    }
                )
            )
            ->willReturnArgument(0);

        if ($translatedMessage) {
            $this->translator
                ->expects($this->once())
                ->method('trans')
                ->with($translatedMessage, [], 'DrawMessenger')
                ->willReturn($message = uniqid('translation-'));
        } else {
            $this->request->setSession($this->createMock(SessionInterface::class));
            $this->translator
                ->expects($this->never())
                ->method('trans');
        }

        $response = $this->controller->click($messageId, $this->request);

        if ($translatedMessage) {
            $this->assertSame(
                [
                    'error' => [$message],
                ],
                $this->request->getSession()->getFlashBag()->all()
            );
        }

        $this->assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        $this->assertSame(
            '/',
            $response->getTargetUrl()
        );
    }

    public function testClick(): void
    {
        $transportName = uniqid('transport-');

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(new Envelope((object) [], [new FindFromTransportStamp($transportName)]));

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (Envelope $envelope) use ($transportName) {
                    $this->assertSame(
                        $transportName,
                        $envelope->last(ReceivedStamp::class)->getTransportName()
                    );

                    return true;
                })
            )->willReturn(
                $envelope = new Envelope(
                    (object) [],
                    [new TransportMessageIdStamp($messageId), new HandledStamp(null, uniqid('handler-'))]
                )
            );

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('link.processed', [], 'DrawMessenger')
            ->willReturn($message = uniqid('translation-'));

        $this->transportLocator
            ->expects($this->once())
            ->method('get')
            ->with($transportName)
            ->willReturn($transport = $this->createMock(TransportInterface::class));

        $transport
            ->expects($this->once())
            ->method('ack')
            ->with($envelope);

        $response = $this->controller->click($messageId, $this->request);

        $this->assertSame(
            [
                'success' => [$message],
            ],
            $this->request->getSession()->getFlashBag()->all()
        );

        $this->assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        $this->assertSame(
            '/',
            $response->getTargetUrl()
        );
    }

    public function testClickWithResponse(): void
    {
        $transportName = uniqid('transport-');

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(new Envelope((object) [], [new FindFromTransportStamp($transportName)]));

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(
                $envelope = new Envelope(
                    (object) [],
                    [
                        new TransportMessageIdStamp($messageId),
                        new HandledStamp($response = new Response(), uniqid('handler-')),
                    ]
                )
            );

        $this->translator
            ->expects($this->never())
            ->method('trans');

        $this->transportLocator
            ->expects($this->once())
            ->method('get')
            ->with($transportName)
            ->willReturn($transport = $this->createMock(TransportInterface::class));

        $transport
            ->expects($this->once())
            ->method('ack')
            ->with($envelope);

        $this->assertSame(
            $response,
            $this->controller->click($messageId, $this->request)
        );
    }

    public function testClickInvalidHandler(): void
    {
        $transportName = uniqid('transport-');

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(
                new Envelope(
                    (object) [],
                    [new FindFromTransportStamp($transportName)]
                )
            );

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(
                new Envelope(
                    (object) [],
                    [
                        new TransportMessageIdStamp($messageId),
                        new HandledStamp(null, $handler1 = uniqid('handler-1-')),
                        new HandledStamp(null, $handler2 = uniqid('handler-2-')),
                    ]
                )
            );

        $this->translator
            ->expects($this->never())
            ->method('trans');

        $response = new Response();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
                    function (MessageLinkErrorEvent $event) use ($response, $handler1, $handler2) {
                        $this->assertInstanceOf(
                            LogicException::class,
                            $error = $event->getError()
                        );

                        $this->assertSame(
                            'Message of type "stdClass" was handled 2 time(s). Only one handler is expected, got: "'.$handler1.'", "'.$handler2.'".',
                            $error->getMessage()
                        );

                        $event->setResponse($response);

                        return true;
                    }
                )
            )
            ->willReturnArgument(0);

        $this->assertSame(
            $response,
            $this->controller->click($messageId, $this->request)
        );
    }
}
