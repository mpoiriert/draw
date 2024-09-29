<?php

namespace App\Tests\MessageHandler;

use App\Message\NewTestDocumentMessage;
use App\Message\NewUserMessage;
use App\MessageHandler\NullMessageHandler;
use Draw\Bundle\TesterBundle\Messenger\MessageHandlerAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class NullMessageHandlerTest extends KernelTestCase
{
    use MessageHandlerAssertionTrait;

    public function testHandlerConfiguration(): void
    {
        $this->assertHandlerMessageConfiguration(
            NullMessageHandler::class,
            [
                NewUserMessage::class => ['handleNewUserMessage'],
                NewTestDocumentMessage::class => ['handleNewTestDocumentMessage'],
            ]
        );
    }
}
