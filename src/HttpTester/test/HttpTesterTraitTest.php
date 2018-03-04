<?php

namespace Draw\HttpTester;

use PHPUnit\Framework\TestCase;

class HttpTesterTraitTest extends TestCase implements ClientFactoryInterface
{
    use HttpTesterTrait;

    private $createClientHasBeenCalled = false;

    public function createClient()
    {
        $this->createClientHasBeenCalled = true;
        return $this->getBridgeClientFactory()->createClient();
    }

    public function testSeUp()
    {
        $this->assertTrue($this->createClientHasBeenCalled);
        $this->assertInstanceOf(ClientInterface::class, static::$client);

        return static::$client;
    }

    /**
     * @depends testSeUp
     *
     * @param Client $client
     * @return Client
     */
    public function testSetUpClient(Client $client)
    {
        $this->assertSame(
            $client,
            $this->setUpClient()
        );
    }

    public function testNewClient()
    {
        $this->assertInstanceOf(ClientInterface::class, static::$client);

        $client = $this->newClient();

        $this->assertInstanceOf(ClientInterface::class, $client);

        $this->assertNotSame(
            static::$client,
            $client
        );
    }
}