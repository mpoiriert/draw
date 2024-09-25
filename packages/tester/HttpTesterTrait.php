<?php

namespace Draw\Component\Tester;

use Draw\Component\Tester\Http\ClientInterface;
use PHPUnit\Framework\Attributes\BeforeClass;

trait HttpTesterTrait
{
    protected static ?ClientInterface $httpTesterClient = null;

    public function httpTester(): ClientInterface
    {
        if (null === static::$httpTesterClient) {
            static::$httpTesterClient = $this->createHttpTesterClient();
        }

        return static::$httpTesterClient;
    }

    #[BeforeClass]
    public static function clearHttpTesterClient(): void
    {
        static::$httpTesterClient = null;
    }

    abstract protected function createHttpTesterClient(): ClientInterface;
}
