<?php

namespace Draw\HttpTester;

trait HttpTesterTrait
{
    /**
     * @var ClientInterface
     */
    protected static $client;

    /**
     * @beforeClass
     */
    static public function setUpClient()
    {
        return static::$client = static::createClient();
    }

    static public function createClient()
    {
        return new Client();
    }
}