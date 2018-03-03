<?php

namespace Draw\HttpTester;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CurlRequestExecutioner implements RequestExecutionerInterface
{
    /**
     * @var \pdeans\Http\Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new \pdeans\Http\Client();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function executeRequest(RequestInterface $request)
    {
        return $this->client->sendRequest($request);
    }
}