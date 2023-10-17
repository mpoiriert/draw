<?php

namespace App\Tests\Controller;

use Draw\Bundle\TesterBundle\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PingActionTest extends WebTestCase
{
    public function testPing(): void
    {
        $client = static::createClient();

        $client->request('GET', '/ping');

        static::assertResponseStatusCodeSame(200);

        static::assertResponseJsonAgainstFile(
            __DIR__.'/fixtures/PingActionTest/testPingWithContext_ping.json',
        );
    }

    public static function provideTestPingWithContext(): iterable
    {
        yield 'error' => ['error', Response::HTTP_BAD_GATEWAY];

        yield 'ping' => ['ping', Response::HTTP_OK];

        yield 'not-configured' => ['not-configured', Response::HTTP_OK];

        yield 'unknown' => ['unknown', Response::HTTP_MULTI_STATUS];
    }

    /**
     * @dataProvider provideTestPingWithContext
     */
    public function testPingWithContext(string $context, int $statusCode): void
    {
        $client = static::createClient();

        $client->request('GET', '/ping/'.$context);

        static::assertResponseStatusCodeSame($statusCode);

        static::assertResponseJsonAgainstFile(
            __DIR__.'/fixtures/PingActionTest/testPingWithContext_'.$context.'.json',
        );
    }
}
