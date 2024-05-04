<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\Messenger\Controller;

use App\Entity\MessengerMessage;
use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageController extends CRUDController
{
    public function retryAction(
        MessengerMessage $message,
        MessageBusInterface $messageBus
    ) {
        if ('failed' !== $message->getQueueName()) {
            $this->addFlash(
                'sonata_flash_error',
                $this->trans('message_cannot_be_retried')
            );

            return $this->redirectToList();
        }

        $messageBus->dispatch(
            new RetryFailedMessageMessage($message->getId())
        );

        $this->addFlash(
            'sonata_flash_success',
            $this->trans('retry_message_successfully_dispatched')
        );

        return $this->redirectToList();
    }
}
