<?php

namespace App\Tests\Controller;

use Draw\Bundle\TesterBundle\JsonResponseAssertionsTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Bundle\TesterBundle\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class PingActionTest extends WebTestCase implements AutowiredInterface
{
    use JsonResponseAssertionsTrait;

    #[AutowireClient]
    private KernelBrowser $client;

    public function testPing(): void
    {
        $this->client->request('GET', '/ping');

        static::assertResponseStatusCodeSame(207);

        static::assertResponseJsonAgainstFile(
            __DIR__.'/fixtures/PingActionTest/testPingWithContext_ping.json',
        );
    }

    public static function provideTestPingWithContext(): iterable
    {
        yield 'error' => ['error', Response::HTTP_BAD_GATEWAY];

        yield 'ping' => ['ping', Response::HTTP_MULTI_STATUS];

        yield 'not-configured' => ['not-configured', Response::HTTP_MULTI_STATUS];

        yield 'unknown' => ['unknown', Response::HTTP_MULTI_STATUS];
    }

    /**
     * @dataProvider provideTestPingWithContext
     */
    public function testPingWithContext(string $context, int $statusCode): void
    {
        $this->client->request('GET', '/ping/'.$context);

        static::assertResponseStatusCodeSame($statusCode);

        static::assertResponseJsonAgainstFile(
            __DIR__.'/fixtures/PingActionTest/testPingWithContext_'.$context.'.json',
        );
    }
}
