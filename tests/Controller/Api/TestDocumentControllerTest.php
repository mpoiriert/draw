<?php

namespace App\Tests\Controller\Api;

use App\Message\NewTestDocumentMessage;
use Draw\Bundle\TesterBundle\Messenger\MessengerTesterTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class TestDocumentControllerTest extends WebTestCase implements AutowiredInterface
{
    use MessengerTesterTrait;

    #[AutowireClient]
    private KernelBrowser $client;

    public function testCreate(): void
    {
        $this->client
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
