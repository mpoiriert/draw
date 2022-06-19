<?php

namespace Draw\Component\Tester;

use Draw\Component\Tester\Http\ClientInterface;

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

    /**
     * @beforeClass
     */
    public static function clearHttpTesterClient()
    {
        static::$httpTesterClient = null;
    }

    abstract protected function createHttpTesterClient(): ClientInterface;
}
