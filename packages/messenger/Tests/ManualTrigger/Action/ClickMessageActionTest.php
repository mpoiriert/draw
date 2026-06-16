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
            static::createStub(TransportRepository::class)
        );

        $messageBus
            ->expects(static::never())
            ->method('dispatch')
            ->seal()
        ;

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willThrowException(new MessageNotFoundException($messageId))
            ->seal()
        ;

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(
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
            ->seal()
        ;

        if ($translatedMessage) {
            $translator
                ->expects(static::once())
                ->method('trans')
                ->with($translatedMessage, [], 'DrawMessenger')
                ->willReturn($message = uniqid('translation-'))
                ->seal()
            ;
        } else {
            $this->request->setSession($this->createMock(SessionInterface::class));
            $translator
                ->expects(static::never())
                ->method('trans')
                ->seal()
            ;
        }

        $response = \call_user_func($object, $messageId, $this->request);

        if ($translatedMessage) {
            static::assertSame(
                [
                    'error' => [$message],
                ],
                $this->request->getSession()->getFlashBag()->all()
            );
        }

        static::assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        static::assertSame(
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
            static::createStub(EventDispatcherInterface::class),
            $translator = $this->createMock(TranslatorInterface::class),
            $transportRepository = $this->createMock(TransportRepository::class)
        );

        $transportName = uniqid('transport-');

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(new Envelope((object) [], [new FoundFromTransportStamp($transportName)]))
            ->seal()
        ;

        $messageBus
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (Envelope $envelope) use ($transportName) {
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
            ->seal()
        ;

        $translator
            ->expects(static::once())
            ->method('trans')
            ->with('link.processed', [], 'DrawMessenger')
            ->willReturn($message = uniqid('translation-'))
            ->seal()
        ;

        $transportRepository
            ->expects(static::once())
            ->method('get')
            ->with($transportName)
            ->willReturn($transport = $this->createMock(TransportInterface::class))
            ->seal()
        ;

        $transport
            ->expects(static::once())
            ->method('ack')
            ->with($envelope)
            ->seal()
        ;

        $response = \call_user_func($object, $messageId, $this->request);

        static::assertSame(
            [
                'success' => [$message],
            ],
            $this->request->getSession()->getFlashBag()->all()
        );

        static::assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        static::assertSame(
            '/',
            $response->getTargetUrl()
        );
    }

    public function testClickWithResponse(): void
    {
        $object = new ClickMessageAction(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            static::createStub(EventDispatcherInterface::class),
            $translator = $this->createMock(TranslatorInterface::class),
            $transportRepository = $this->createMock(TransportRepository::class)
        );

        $transportName = uniqid('transport-');

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(new Envelope((object) [], [new FoundFromTransportStamp($transportName)]))
            ->seal()
        ;

        $messageBus
            ->expects(static::once())
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
            ->seal()
        ;

        $translator
            ->expects(static::never())
            ->method('trans')
            ->seal()
        ;

        $transportRepository
            ->expects(static::once())
            ->method('get')
            ->with($transportName)
            ->willReturn($transport = $this->createMock(TransportInterface::class))
            ->seal()
        ;

        $transport
            ->expects(static::once())
            ->method('ack')
            ->with($envelope)
            ->seal()
        ;

        static::assertSame(
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
            static::createStub(TransportRepository::class)
        );

        $transportName = uniqid('transport-');

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId = uniqid('message-Id'))
            ->willReturn(
                new Envelope(
                    (object) [],
                    [new FoundFromTransportStamp($transportName)]
                )
            )
            ->seal()
        ;

        $messageBus
            ->expects(static::once())
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
            ->seal()
        ;

        $translator
            ->expects(static::never())
            ->method('trans')
            ->seal()
        ;

        $response = new Response();

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(
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
            ->seal()
        ;

        static::assertSame(
            $response,
            \call_user_func($object, $messageId, $this->request)
        );
    }
}
