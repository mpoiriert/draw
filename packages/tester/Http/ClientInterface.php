<?php

namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

interface ClientInterface
{
    public function getRequestExecutioner(): RequestExecutionerInterface;

    public function setRequestExecutioner(RequestExecutionerInterface $requestExecutioner);

    /**
     * Register a observer that can be hooked in different step of the request flow.
     *
     * @param int $position The position in which the observer must be registered. Lower number are executed first
     */
    public function registerObserver(ClientObserver $clientObserver, int $position = 0): void;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a GET method.
     */
    public function get(string $uri, array $headers = [], string $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a HEAD method.
     */
    public function head(string $uri, array $headers = [], string $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a PUT method.
     *
     * @param string|resource|StreamInterface|null $body
     */
    public function put(string $uri, $body, array $headers = [], string $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a POST method.
     *
     * @param string|resource|StreamInterface|null $body
     */
    public function post(string $uri, $body, array $headers = [], string $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a PATCH method.
     *
     * @param string|resource|StreamInterface|null $body
     */
    public function patch(string $uri, $body, array $headers = [], string $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a DELETE method.
     */
    public function delete(string $uri, array $headers = [], string $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a OPTIONS method.
     */
    public function options(string $uri, array $headers = [], string $version = '1.1'): TestResponse;

    public function send(RequestInterface $request): TestResponse;

    /**
     * This method will create a RequestInterface that can be use for the ClientInterface::send method.
     *
     * @param string|resource|StreamInterface|null $body
     */
    public function createRequest(string $method, string $uri, $body = null, array $headers = [], string $version = '1.1'): RequestInterface;
}
