<?php

namespace Draw\Component\Tester\Tests;

use Draw\Component\Tester\Http\Client;
use Draw\Component\Tester\Http\ClientInterface;
use Draw\Component\Tester\Http\CurlRequestExecutioner;
use Draw\Component\Tester\HttpTesterTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class HttpTesterTraitTest extends TestCase
{
    use HttpTesterTrait;

    private bool $createClientHasBeenCalled = false;

    public function createHttpTesterClient(): ClientInterface
    {
        $this->createClientHasBeenCalled = true;

        return new Client(new CurlRequestExecutioner());
    }

    public function testHttpTester(): void
    {
        $client = $this->httpTester();
        static::assertInstanceOf(ClientInterface::class, $client);
        static::assertSame(static::$httpTesterClient, $client);
        static::assertTrue($this->createClientHasBeenCalled);

        static::assertSame($client, $this->httpTester());
    }

    #[Depends('testHttpTester')]
    public function testClearHttpTesterClient(): void
    {
        $this->clearHttpTesterClient();
        static::assertNull(static::$httpTesterClient);
    }
}
