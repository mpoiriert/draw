<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\Messenger\Controller;

use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageController extends CRUDController
{
    public function retryAction(
        Request $request,
        MessageBusInterface $messageBus,
    ): Response {
        $message = $this->assertObjectExists($request, true);

        \assert($message instanceof DrawMessageInterface);

        if ('failed' !== $message->getQueueName()) {
            $this->addFlash(
                'sonata_flash_error',
                $this->trans('message_cannot_be_retried')
            );

            return $this->redirectToList();
        }

        $messageBus->dispatch(
            new RetryFailedMessageMessage($message->getMessageId())
        );

        $this->addFlash(
            'sonata_flash_success',
            $this->trans('retry_message_successfully_dispatched')
        );

        return $this->redirectToList();
    }
}
