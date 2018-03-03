<?php

namespace Draw\HttpTester;

use PHPUnit\Framework\TestCase;

class HttpTesterTraitTest extends TestCase
{
    use HttpTesterTrait {
        createClient as parentCreateClient;
    }

    public static function createClient()
    {
        return self::parentCreateClient();
    }

    public function testBeforeClass()
    {
        $this->assertInstanceOf(ClientInterface::class, static::$client);

        return static::$client;
    }

    /**
     * @depends testBeforeClass
     *
     * @param Client $oldClient
     * @return Client
     */
    public function testSetUpClient(Client $oldClient)
    {
        $client = static::setUpClient();

        $this->assertInstanceOf(ClientInterface::class, $client);

        $this->assertNotSame(
            $oldClient,
            $client
        );

        $this->assertSame(
            static::$client,
            $client
        );

        return $client;
    }

    public function testCreateClient()
    {
        $this->assertInstanceOf(ClientInterface::class, static::$client);

        $client = static::createClient();

        $this->assertInstanceOf(ClientInterface::class, $client);

        $this->assertNotSame(
            static::$client,
            static::createClient()
        );
    }
}