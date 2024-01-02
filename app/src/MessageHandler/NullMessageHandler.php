<?php

namespace App\MessageHandler;

use App\Message\NewTestDocumentMessage;
use App\Message\NewUserMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Empty handler just to prevent "No handler for message" exception.
 */
class NullMessageHandler
{
    #[AsMessageHandler]
    public function handleNewUserMessage(NewUserMessage $message): void
    {
    }

    #[AsMessageHandler]
    public function handleNewTestDocumentMessage(NewTestDocumentMessage $message): void
    {
    }
}
