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
    public function registerObserver(ClientObserver $clientObserver, $position = 0): void;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a GET method.
     *
     * @param $uri
     * @param string $version
     */
    public function get($uri, array $headers = [], $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a HEAD method.
     *
     * @param $uri
     * @param string $version
     */
    public function head($uri, array $headers = [], $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a PUT method.
     *
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function put($uri, $body, array $headers = [], $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a POST method.
     *
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function post($uri, $body, array $headers = [], $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a PATCH method.
     *
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function patch($uri, $body, array $headers = [], $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a DELETE method.
     *
     * @param $uri
     * @param string $version
     */
    public function delete($uri, array $headers = [], $version = '1.1'): TestResponse;

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a OPTIONS method.
     *
     * @param $uri
     * @param string $version
     */
    public function options($uri, array $headers = [], $version = '1.1'): TestResponse;

    public function send(RequestInterface $request): TestResponse;

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
