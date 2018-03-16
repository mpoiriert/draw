<?php

namespace Draw\HttpTester;

use Draw\HttpTester\Bridge\Laravel4\Laravel4RequestExecutioner;
use Draw\HttpTester\Bridge\Laravel4\Laravel4TestContextInterface;

class BridgeClientFactory
{
    private $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function createClient()
    {
        switch (true) {
            case $this->context instanceof Laravel4TestContextInterface:
                $requestExecutioner = new Laravel4RequestExecutioner($this->context);
                break;
            default:
                $requestExecutioner = new CurlRequestExecutioner();
                break;
        }

        return new Client($requestExecutioner);
    }
}