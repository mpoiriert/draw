<?php

namespace App\MessageHandler;

use App\Message\NewUserMessage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class NewUserMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield NewUserMessage::class => 'handleNewUserMessage';
    }

    /**
     * Empty method just to prevent error.
     */
    public function handleNewUserMessage(NewUserMessage $message): void
    {
    }
}
