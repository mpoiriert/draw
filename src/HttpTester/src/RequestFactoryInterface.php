<?php

namespace Draw\HttpTester;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

interface RequestFactoryInterface
{
    /**
     * This method will create a RequestInterface that can be use for the ClientInterface::send method
     *
     * @param $method
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return RequestInterface
     */
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1');
}