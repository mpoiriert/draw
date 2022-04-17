<?php

namespace Draw\Component\Messenger\Controller;

use Draw\Component\Messenger\EnvelopeFinder;
use Draw\Component\Messenger\Event\MessageLinkErrorEvent;
use Draw\Component\Messenger\Exception\MessageExpiredException;
use Draw\Component\Messenger\Exception\MessageNotFoundException;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Stamp\FindFromTransportStamp;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class MessageController
{
    public const MESSAGE_ID_PARAMETER_NAME = 'dMUuid';

    private MessageBusInterface $messageBus;

    private EnvelopeFinder $enveloperFinder;

    private EventDispatcherInterface $eventDispatcher;

    private TranslatorInterface $translator;

    private ContainerInterface $transportLocator;

    public function __construct(
        MessageBusInterface $messageBus,
        EnvelopeFinder $enveloperFinder,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ContainerInterface $transportLocator
    ) {
        $this->messageBus = $messageBus;
        $this->enveloperFinder = $enveloperFinder;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->transportLocator = $transportLocator;
    }

    /**
     * @Route(name="message_click", methods={"GET"}, path="/message-link/{dMUuid}")
     */
    public function click(
        string $dMUuid,
        Request $request
    ): Response {
        try {
            switch (true) {
                case null === $envelope = $this->enveloperFinder->findById($dMUuid):
                case null !== $envelope->last(SentToFailureTransportStamp::class):
                    throw new MessageNotFoundException($dMUuid);
            }

            if ($expirationStamp = $envelope->last(ExpirationStamp::class)) {
                if ($expirationStamp->getDateTime()->getTimestamp() < time()) {
                    throw new MessageExpiredException($dMUuid, $expirationStamp->getDateTime());
                }
            }

            $result = $this->handle($envelope);

            if ($result instanceof Response) {
                return $result;
            }

            $this->addFlash($request, 'success', 'link.processed');
        } catch (Throwable $error) {
            $response = $this->eventDispatcher
                ->dispatch(new MessageLinkErrorEvent($request, $dMUuid, $error))
                ->getResponse();

            switch (true) {
                case $response:
                    return $response;
                case $error instanceof MessageNotFoundException:
                case $error instanceof MessageExpiredException:
                    $this->addFlash($request, 'error', 'link.invalid');
                    break;
            }
        }

        return new RedirectResponse('/');
    }

    private function addFlash(Request $request, string $type, string $message): void
    {
        switch (true) {
            case !$request->hasSession():
            case null === $session = $request->getSession():
            case !$session instanceof Session:
                return;
        }

        $session->getFlashBag()->add($type, $this->translator->trans($message, [], 'DrawMessenger'));
    }

    /**
     * @return mixed
     */
    private function handle(Envelope $envelope)
    {
        $envelope = $this->messageBus->dispatch(
            $envelope->with(
                new ReceivedStamp($transportName = $envelope->last(FindFromTransportStamp::class)->getTransportName())
            )
        );

        $handledStamps = $envelope->all(HandledStamp::class);

        if (1 !== \count($handledStamps)) {
            $handlers = implode(
                ', ',
                array_map(
                    function (HandledStamp $stamp): string {
                        return sprintf('"%s"', $stamp->getHandlerName());
                    },
                    $handledStamps
                )
            );

            throw new LogicException(sprintf('Message of type "%s" was handled %d time(s). Only one handler is expected, got: %s.', get_debug_type($envelope->getMessage()), \count($handledStamps), $handlers));
        }

        $this->transportLocator
            ->get($transportName)
            ->ack($envelope);

        return $handledStamps[0]->getResult();
    }
}
