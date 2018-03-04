<?php

namespace Draw\HttpTester;

use Draw\HttpTester\Bridge\Laravel4\Laravel4RequestExecutioner;
use Draw\HttpTester\Bridge\Laravel4\Laravel4TestContextInterface;
use PHPUnit\Framework\TestCase;

class BridgeClientFactory
{
    private $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function createClient()
    {
        switch (true) {
            case $this->testCase instanceof Laravel4TestContextInterface:
                $requestExecutioner = new Laravel4RequestExecutioner($this->testCase);
                break;
            default:
                $requestExecutioner = new CurlRequestExecutioner();
                break;
        }

        return new Client($requestExecutioner);
    }
}