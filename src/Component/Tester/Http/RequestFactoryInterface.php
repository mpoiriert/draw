<?php

namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

interface RequestFactoryInterface
{
    /**
     * This method will create a RequestInterface that can be use for the ClientInterface::send method.
     *
     * @param $method
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1'): RequestInterface;
}
