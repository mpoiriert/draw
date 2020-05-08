<?php namespace Draw\Component\Tester;

use Draw\Component\Tester\Http\ClientInterface;

trait HttpTesterTrait
{
    /**
     * @var ClientInterface
     */
    protected static $httpTesterClient;

    public function httpTester(): ClientInterface
    {
        if (static::$httpTesterClient === null) {
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