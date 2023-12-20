<?php

namespace Draw\Bundle\UserBundle\MessageHandler;

use Draw\Bundle\UserBundle\Message\NewUserMessage;
use Draw\Bundle\UserBundle\Message\PasswordChangeRequestedMessage;
use Draw\Bundle\UserBundle\Message\TemporaryUnlockedMessage;
use Draw\Bundle\UserBundle\Message\UserLockActivatedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class PreventNotHandleMessageHandler
{
    #[AsMessageHandler]
    public function nothing(PasswordChangeRequestedMessage|NewUserMessage|UserLockActivatedMessage|TemporaryUnlockedMessage $event): void
    {
    }
}
