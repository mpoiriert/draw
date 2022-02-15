<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Message\PasswordChangeRequestedMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class PreventNotHandleMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield PasswordChangeRequestedMessage::class => 'nothing';
    }

    public function nothing(object $event): void
    {
    }
}
