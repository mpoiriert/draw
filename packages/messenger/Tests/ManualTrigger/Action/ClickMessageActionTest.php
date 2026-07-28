<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\Action;

use Draw\Component\Messenger\ManualTrigger\Action\ClickMessageAction;
use Draw\Component\Messenger\ManualTrigger\Event\MessageLinkErrorEvent;
use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
use Draw\Component\Messenger\Searchable\TransportRepository;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
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
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[CoversClass(ClickMessageAction::class)]
class ClickMessageActionTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->request->setSession(
            new Session(
                new MockArraySessionStorage(),
            )
        );
    }

    #[DataProvider('provideClickEnvelopeErrorCases')]
    public function testClickEnvelopeError(
        string $exceptionClass,
        ?string $translatedMessage,
    ): void {
        $object = new ClickMessageAction(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $translator = $this->createMock(TranslatorInterface::class),
            $this->createStub(TransportRepository::class)
        );

        $messageBus
            ->expects($this->never())
            ->method('dispatch')
        ;

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willThrowException(new MessageNotFoundException($messageId))
        ;

        $eventDispatcher
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
            ->willReturnArgument(0)
        ;

        if ($translatedMessage) {
            $translator
                ->expects($this->once())
                ->method('trans')
                ->with($translatedMessage, [], 'DrawMessenger')
                ->willReturn($message = uniqid('translation-'))
            ;
        } else {
            $this->request->setSession($this->createStub(SessionInterface::class));
            $translator
                ->expects($this->never())
                ->method('trans')
            ;
        }

        $response = \call_user_func($object, $messageId, $this->request);

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

    public static function provideClickEnvelopeErrorCases(): iterable
    {
        yield 'not-found' => [
            MessageNotFoundException::class,
            'link.invalid',
        ];

        yield 'error-queue' => [
            MessageNotFoundException::class,
            'link.invalid',
        ];

        yield 'expired' => [
            MessageNotFoundException::class,
            'link.invalid',
        ];
    }

    public function testClick(): void
    {
        $object = new ClickMessageAction(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $this->createStub(EventDispatcherInterface::class),
            $translator = $this->createMock(TranslatorInterface::class),
            $transportRepository = $this->createMock(TransportRepository::class)
        );

        $transportName = uniqid('transport-');

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(new Envelope((object) [], [new FoundFromTransportStamp($transportName)]))
        ;

        $messageBus
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
            )
            ->willReturn(
                $envelope = new Envelope(
                    (object) [],
                    [new TransportMessageIdStamp($messageId), new HandledStamp(null, uniqid('handler-'))]
                )
            )
        ;

        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('link.processed', [], 'DrawMessenger')
            ->willReturn($message = uniqid('translation-'))
        ;

        $transportRepository
            ->expects($this->once())
            ->method('get')
            ->with($transportName)
            ->willReturn($transport = $this->createMock(TransportInterface::class))
        ;

        $transport
            ->expects($this->once())
            ->method('ack')
            ->with($envelope)
        ;

        $response = \call_user_func($object, $messageId, $this->request);

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
        $object = new ClickMessageAction(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $this->createStub(EventDispatcherInterface::class),
            $translator = $this->createMock(TranslatorInterface::class),
            $transportRepository = $this->createMock(TransportRepository::class)
        );

        $transportName = uniqid('transport-');

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(new Envelope((object) [], [new FoundFromTransportStamp($transportName)]))
        ;

        $messageBus
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
            )
        ;

        $translator
            ->expects($this->never())
            ->method('trans')
        ;

        $transportRepository
            ->expects($this->once())
            ->method('get')
            ->with($transportName)
            ->willReturn($transport = $this->createMock(TransportInterface::class))
        ;

        $transport
            ->expects($this->once())
            ->method('ack')
            ->with($envelope)
        ;

        $this->assertSame(
            $response,
            \call_user_func($object, $messageId, $this->request)
        );
    }

    public function testClickInvalidHandler(): void
    {
        $object = new ClickMessageAction(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $translator = $this->createMock(TranslatorInterface::class),
            $this->createStub(TransportRepository::class)
        );

        $transportName = uniqid('transport-');

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(
                new Envelope(
                    (object) [],
                    [new FoundFromTransportStamp($transportName)]
                )
            )
        ;

        $messageBus
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
            )
        ;

        $translator
            ->expects($this->never())
            ->method('trans')
        ;

        $response = new Response();

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
                    function (MessageLinkErrorEvent $event) use ($response, $handler1, $handler2) {
                        $this->assertInstanceOf(
                            \LogicException::class,
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
            ->willReturnArgument(0)
        ;

        $this->assertSame(
            $response,
            \call_user_func($object, $messageId, $this->request)
        );
    }
}
