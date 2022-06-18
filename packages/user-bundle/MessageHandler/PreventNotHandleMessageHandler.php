<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Draw\Bundle\UserBundle\Message\NewUserMessage;
use Draw\Bundle\UserBundle\Message\PasswordChangeRequestedMessage;
use Draw\Bundle\UserBundle\Message\TemporaryUnlockedMessage;
use Draw\Bundle\UserBundle\Message\UserLockActivatedMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class PreventNotHandleMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield PasswordChangeRequestedMessage::class => 'nothing';
        yield NewUserMessage::class => 'nothing';
        yield UserLockActivatedMessage::class => 'nothing';
        yield TemporaryUnlockedMessage::class => 'nothing';
    }

    public function nothing(object $event): void
    {
    }
}
