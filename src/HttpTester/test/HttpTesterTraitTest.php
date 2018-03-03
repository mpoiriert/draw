<?php

namespace Draw\HttpTester;

use PHPUnit\Framework\TestCase;

class HttpTesterTraitTest extends TestCase
{
    use HttpTesterTrait;

    public function testBeforeClass()
    {
        $this->assertInstanceOf(ClientInterface::class, static::$client);

        return static::$client;
    }

    /**
     * @depends testBeforeClass
     *
     * @param Client $oldClient
     */
    public function testCreateClient(Client $oldClient)
    {
        $client = static::createClient();

        $this->assertInstanceOf(ClientInterface::class, $client);

        $this->assertNotSame(
            $oldClient,
            $client
        );
    }
}