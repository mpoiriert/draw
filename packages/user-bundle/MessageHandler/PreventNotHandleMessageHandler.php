<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Draw\Bundle\UserBundle\AccountLocker\Message\UserLockActivatedMessage;
use Draw\Bundle\UserBundle\Onboarding\Message\NewUserMessage;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Message\PasswordChangeRequestedMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class PreventNotHandleMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield PasswordChangeRequestedMessage::class => 'nothing';
        yield NewUserMessage::class => 'nothing';
        yield UserLockActivatedMessage::class => 'nothing';
    }

    public function nothing(object $event): void
    {
    }
}
