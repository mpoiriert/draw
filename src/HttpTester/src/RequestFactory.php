<?php

namespace Draw\HttpTester;

use GuzzleHttp\Psr7\Request;

class RequestFactory implements RequestFactoryInterface
{
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1')
    {
        return new Request($method, $uri, $headers, $body, $version);
    }
}