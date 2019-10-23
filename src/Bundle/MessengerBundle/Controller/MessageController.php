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
    /**
     * @Route(name="message-click", methods={"GET"}, path="/message-click/{id}")
     *
     * @param MessageBus $messengerBusDraw
     * @param $id
     *
     * @return Response
     */
    public function click($id, DrawTransport $drawTransport, MessageBusInterface $messengerBusDraw)
    {
        $envelope = $drawTransport->find($id);
        $envelope = $messengerBusDraw->dispatch($envelope->with(new ReceivedStamp('messenger.transport.draw')));

        $message = $envelope->getMessage();
        if($message instanceof RedirectMessageInterface && $url = $message->getUrlToRedirectTo()) {
            $drawTransport->ack($envelope);
            return new RedirectResponse($message->getUrlToRedirectTo());
        }
    }
}