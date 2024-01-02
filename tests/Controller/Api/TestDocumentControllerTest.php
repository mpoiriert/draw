<?php

namespace App\Tests\Controller\Api;

use App\Message\NewTestDocumentMessage;
use Draw\Bundle\TesterBundle\Messenger\MessengerTesterTrait;
use Draw\Bundle\TesterBundle\WebTestCase;

class TestDocumentControllerTest extends WebTestCase
{
    use MessengerTesterTrait;

    public function testCreate(): void
    {
        static::createClient()
            ->jsonRequest(
                'POST',
                '/api/test-documents',
            );

        static::assertResponseIsSuccessful();

        static::getJsonResponseDataTester()
            ->path('id')
            ->assertIsString();

        static::getTransportTester('sync')
            ->assertMessageMatch(NewTestDocumentMessage::class);
    }
}
