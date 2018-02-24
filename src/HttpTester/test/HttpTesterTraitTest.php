<?php

namespace Draw\HttpTester;

use PHPUnit\Framework\TestCase;

class HttpTesterTraitTest extends TestCase
{
    use HttpTesterTrait;

    public function testSetupBeforeClass()
    {
        $this->assertInstanceOf(ClientInterface::class, static::$client);
    }
}