<?php

namespace Draw\Component\Tester\Tests\Http;

use Draw\Component\Tester\Http\ClientObserver;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientObserverTest extends TestCase
{
    public function testPreSendRequest(): void
    {
        $clientObserver = $this->getMockForAbstractClass(ClientObserver::class);

        $request = new Request('GET', '/test');

        /* @var ClientObserver $clientObserver */
        static::assertSame(
            $request,
            $clientObserver->preSendRequest($request)
        );
    }

    public function testPostSendRequest(): void
    {
        $clientObserver = $this->getMockForAbstractClass(ClientObserver::class);

        $response = new Response(200);

        /* @var ClientObserver $clientObserver */
        static::assertSame(
            $response,
            $clientObserver->postSendRequest(new Request('GET', '/test'), $response)
        );
    }
}
