<?php namespace Draw\Bundle\MessengerBundle\Controller;

use Draw\Bundle\MessengerBundle\Message\RedirectMessageInterface;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Routing\Annotation\Route;

class MessageController
{
    const MESSAGE_ID_PARAMETER_NAME = 'dMUuid';
    /**
     * @Route(name="message-click", methods={"GET"}, path="/message-click/{dMUuid}")
     *
     * @param MessageBus $messengerBusDraw
     * @param $id
     *
     * @return Response
     */
    public function click($dMUuid, DrawTransport $drawTransport, MessageBusInterface $messengerBusDraw)
    {
        $envelope = $drawTransport->find($dMUuid);
        $envelope = $messengerBusDraw->dispatch($envelope->with(new ReceivedStamp('messenger.transport.draw')));

        $message = $envelope->getMessage();
        if($message instanceof RedirectMessageInterface && $url = $message->getUrlToRedirectTo()) {
            $drawTransport->ack($envelope);
            return new RedirectResponse($message->getUrlToRedirectTo());
        }
    }
}