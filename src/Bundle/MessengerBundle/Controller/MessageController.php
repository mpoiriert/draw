<?php

namespace Draw\Bundle\MessengerBundle\Controller;

use Draw\Bundle\MessengerBundle\Event\ErroredMessageLinkEvent;
use Draw\Bundle\MessengerBundle\Message\ResponseGeneratorMessageInterface;
use Draw\Component\Messenger\Exception\MessageExpiredException;
use Draw\Component\Messenger\Exception\MessageNotFoundException;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageController extends AbstractController
{
    const MESSAGE_ID_PARAMETER_NAME = 'dMUuid';

    /**
     * @Route(name="message_click", methods={"GET"}, path="/message-link/{dMUuid}")
     *
     * @param MessageBus $messengerBusDraw
     * @param $id
     *
     * @return Response
     */
    public function click(
        $dMUuid,
        Request $request,
        DrawTransport $drawTransport,
        MessageBusInterface $messengerBusDraw,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        try {
            if (null === ($envelope = $drawTransport->find($dMUuid))) {
                throw new MessageNotFoundException($dMUuid);
            }

            if ($expirationStamp = $envelope->last(ExpirationStamp::class)) {
                if ($expirationStamp->getDateTime()->getTimestamp() < time()) {
                    throw new MessageExpiredException($dMUuid, $expirationStamp->getDateTime());
                }
            }

            $envelope = $messengerBusDraw->dispatch($envelope->with(new ReceivedStamp('messenger.transport.draw')));

            $drawTransport->ack($envelope);
            $message = $envelope->getMessage();
            if ($message instanceof ResponseGeneratorMessageInterface) {
                return $message->generateResponse();
            }
            $this->addFlash('success', $translator->trans('link.processed', [], 'DrawMessengerBundle'));
        } catch (\Throwable $error) {
            $response = $eventDispatcher
                ->dispatch(new ErroredMessageLinkEvent($request, $dMUuid, $error))
                ->getResponse();
            if ($response) {
                return $response;
            }
            if ($error instanceof MessageNotFoundException) {
                $this->addFlash('error', $translator->trans('link.invalid', [], 'DrawMessengerBundle'));
            } elseif ($error instanceof MessageExpiredException) {
                $this->addFlash('error', $translator->trans('link.expired', [], 'DrawMessengerBundle'));
            }
        }

        return $this->redirectToRoute('index');
    }
}
