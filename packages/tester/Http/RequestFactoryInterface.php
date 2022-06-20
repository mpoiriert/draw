<?php

namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

interface RequestFactoryInterface
{
    /**
     * This method will create a RequestInterface that can be use for the ClientInterface::send method.
     *
     * @param string|resource|StreamInterface|null $body
     */
    public function createRequest(string $method, string $uri, $body = null, array $headers = [], string $version = '1.1'): RequestInterface;
}
