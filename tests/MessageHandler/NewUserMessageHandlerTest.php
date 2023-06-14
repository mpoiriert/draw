<?php

namespace App\Tests\MessageHandler;

use App\Message\NewUserMessage;
use App\MessageHandler\NewUserMessageHandler;
use Draw\Bundle\TesterBundle\Messenger\MessageHandlerAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewUserMessageHandlerTest extends KernelTestCase
{
    use MessageHandlerAssertionTrait;

    public function testHandlerConfiguration(): void
    {
        $this->assertHandlerMessageConfiguration(
            NewUserMessageHandler::class,
            [
                NewUserMessage::class => ['handleNewUserMessage'],
            ]
        );
    }
}
