<?php

namespace Draw\HttpTester;

trait HttpTesterTrait
{
    protected static $client;

    /**
     * @beforeClass
     */
    static public function createClient()
    {
        return static::$client = new Client();
    }
}