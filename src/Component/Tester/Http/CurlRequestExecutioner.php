<?php

namespace Draw\Component\Tester\Http;

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

    public function executeRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
