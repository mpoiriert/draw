<?php

namespace App\Tests\Controller\Api;

use Draw\Bundle\TesterBundle\WebTestCase;

class TestDocumentControllerTest extends WebTestCase
{
    public function testCreate(): void
    {
        static::createClient()
            ->jsonRequest(
                'POST',
                '/api/test-documents',
                [
                ]
            );

        static::assertResponseIsSuccessful();

        static::getJsonResponseDataTester()
            ->path('id')
            ->assertIsString();
    }
}
