<?php namespace Draw\Component\Tester\Tests;

use Draw\Component\Tester\Http\Client;
use Draw\Component\Tester\Http\ClientInterface;
use Draw\Component\Tester\Http\CurlRequestExecutioner;
use Draw\Component\Tester\HttpTesterTrait;
use PHPUnit\Framework\TestCase;

class HttpTesterTraitTest extends TestCase
{
    use HttpTesterTrait;

    private $createClientHasBeenCalled = false;

    public function createHttpTesterClient(): ClientInterface
    {
        $this->createClientHasBeenCalled = true;
        return new Client(new CurlRequestExecutioner());
    }

    public function testHttpTester()
    {
        $client = $this->httpTester();
        $this->assertInstanceOf(ClientInterface::class, $client);
        $this->assertSame(static::$httpTesterClient, $client);
        $this->assertTrue($this->createClientHasBeenCalled);

        $this->assertSame($client, $this->httpTester());
    }

    /**
     * @depends testHttpTester
     */
    public function testClearHttpTesterClient()
    {
        $this->clearHttpTesterClient();
        $this->assertNull(static::$httpTesterClient);
    }
}