<?php

namespace App\Tests\Controller;

use Draw\Bundle\TesterBundle\WebTestCase;

class PingControllerTest extends WebTestCase
{
    public function testPing(): void
    {
        $client = static::createClient();

        $client->request('GET', '/ping');

        static::assertResponseIsSuccessful();

        static::assertSame('pong', static::getResponseContent());
    }
}
