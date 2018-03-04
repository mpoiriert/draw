<?php

namespace Draw\HttpTester;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

interface ClientInterface
{
    /**
     * Register a observer that can be hooked in different step of the request flow
     *
     * @param ClientObserver $clientObserver
     * @return void
     */
    public function registerObserver(ClientObserver $clientObserver);

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a GET method
     *
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function get($uri, array $headers = [], $version = '1.1');

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a HEAD method
     *
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function head($uri, array $headers = [], $version = '1.1');

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a PUT method
     *
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function put($uri, $body, array $headers = [], $version = '1.1');

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a POST method
     *
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function post($uri, $body, array $headers = [], $version = '1.1');

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a PATCH method
     *
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function patch($uri, $body, array $headers = [], $version = '1.1');

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a DELETE method
     *
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function delete($uri, array $headers = [], $version = '1.1');

    /**
     * This is a shortcut method that chain the ClientInterface::createRequest and the ClientInterface::send execution
     * for a OPTIONS method
     *
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function options($uri, array $headers = [], $version = '1.1');

    /**
     * @param RequestInterface $request
     * @return TestResponse
     */
    public function send(RequestInterface $request);

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