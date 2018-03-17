<?php

namespace Draw\HttpTester;

trait HttpTesterTrait
{
    /**
     * @var ClientInterface
     */
    protected static $client;

    /**
     * @var BridgeClientFactory;
     */
    private $bridgeClientFactory;

    /**
     * @beforeClass
     */
    public static function clearClient()
    {
        static::$client = null;
    }

    /**
     * @before
     */
    public function setUpClient()
    {
        if (is_null(static::$client)) {
            static::$client = $this->newClient();
        }

        return static::$client;
    }

    protected function newClient()
    {
        if ($this instanceof ClientFactoryInterface) {
            return $this->createClient();
        }

        return $this->getBridgeClientFactory()->createClient();
    }

    /**
     * @return BridgeClientFactory
     */
    private function getBridgeClientFactory()
    {
        if (is_null($this->bridgeClientFactory)) {
            $this->bridgeClientFactory = new BridgeClientFactory($this);
        }

        return $this->bridgeClientFactory;
    }
}