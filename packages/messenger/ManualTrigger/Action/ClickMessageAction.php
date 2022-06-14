<?php

namespace Draw\Component\Messenger\ManualTrigger\Action;

use Draw\Component\Messenger\ManualTrigger\Event\MessageLinkErrorEvent;
use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Messenger\Searchable\Filter\MustNotBeStampedEnvelopeFilter;
use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class ClickMessageAction
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

    public function __invoke(
        string $dMUuid,
        Request $request
    ): Response {
        try {
            $result = $this->handle(
                $this->enveloperFinder
                    ->findById(
                        $dMUuid,
                        MustNotBeStampedEnvelopeFilter::sentToFailureTransport()
                    )
            );

            if ($result instanceof Response) {
                return $result;
            }

            $this->addFlash($request, 'success', 'link.processed');
        } catch (Throwable $error) {
            $response = $this->eventDispatcher
                ->dispatch(new MessageLinkErrorEvent($request, $dMUuid, $error))
                ->getResponse();

            if ($response) {
                return $response;
            }

            $this->addFlash($request, 'error', 'link.invalid');
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
                new ReceivedStamp($transportName = $envelope->last(FoundFromTransportStamp::class)->getTransportName())
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