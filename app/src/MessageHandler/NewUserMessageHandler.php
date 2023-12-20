<?php

namespace App\MessageHandler;

use App\Message\NewUserMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class NewUserMessageHandler
{
    /**
     * Empty method just to prevent error.
     */
    #[AsMessageHandler]
    public function handleNewUserMessage(NewUserMessage $message): void
    {
    }
}
