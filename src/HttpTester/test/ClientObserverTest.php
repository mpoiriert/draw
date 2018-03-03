<?php

namespace Draw\HttpTester;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientObserverTest extends TestCase
{
    public function testPreSendRequest()
    {
        $clientObserver = $this->getMockForAbstractClass(ClientObserver::class);

        $request = new Request('GET', '/test');

        /** @var ClientObserver $clientObserver */
        $this->assertSame(
            $request,
            $clientObserver->preSendRequest($request)
        );
    }

    public function testPostSendRequest()
    {
        $clientObserver = $this->getMockForAbstractClass(ClientObserver::class);

        $response = new Response(200);

        /** @var ClientObserver $clientObserver */
        $this->assertSame(
            $response,
            $clientObserver->postSendRequest( new Request('GET', '/test'), $response)
        );
    }
}