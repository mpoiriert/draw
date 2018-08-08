<?php

namespace Draw\HttpTester;

use Draw\HttpTester\Bridge\Symfony4;
use Draw\HttpTester\Bridge\Laravel4;

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
            case $this->context instanceof Laravel4\Laravel4TestContextInterface:
                $requestExecutioner = new Laravel4\Laravel4RequestExecutioner($this->context);
                break;
            case $this->context instanceof Symfony4\Symfony4TestContextInterface:
                $requestExecutioner = new Symfony4\Symfony4RequestExecutioner($this->context);
                break;
            default:
                $requestExecutioner = new CurlRequestExecutioner();
                break;
        }

        return new Client($requestExecutioner);
    }
}