<?php

namespace Draw\HttpTester;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class Client implements ClientInterface
{
    private $requestFactory = null;

    private $requestExecutioner = null;

    public function __construct(
        RequestExecutionerInterface $requestExecutioner = null,
        RequestFactoryInterface $requestFactory = null
    ) {
        $this->requestExecutioner = $requestExecutioner ?: new CurlRequestExecutioner();
        $this->requestFactory = $requestFactory ?: new RequestFactory();
    }

    /**
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function get($uri, array $headers = [], $version = '1.1')
    {
       return $this->send(
           $this->createRequest('GET', $uri, null, $headers, $version)
       );
    }

    /**
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function head($uri, array $headers = [], $version = '1.1')
    {
        return $this->send(
            $this->createRequest('HEAD', $uri, null, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function put($uri, $body, array $headers = [], $version = '1.1')
    {
        return $this->send(
            $this->createRequest('PUT', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function post($uri, $body, array $headers = [], $version = '1.1')
    {
        return $this->send(
            $this->createRequest('POST', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function patch($uri, $body, array $headers = [], $version = '1.1')
    {
        return $this->send(
            $this->createRequest('PATCH', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function delete($uri, array $headers = [], $version = '1.1')
    {
        return $this->send(
            $this->createRequest('DELETE', $uri, null, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function options($uri, array $headers = [], $version = '1.1')
    {
        return $this->send(
            $this->createRequest('OPTIONS', $uri, null, $headers, $version)
        );
    }

    /**
     * @param RequestInterface $request
     * @return TestResponse
     */
    public function send(RequestInterface $request)
    {
        return new TestResponse(
            $request,
            $this->requestExecutioner->executeRequest($request)
        );
    }

    /**
     * @param $method
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return mixed|RequestInterface
     */
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1')
    {
        return $this->requestFactory->createRequest(...func_get_args());
    }
}