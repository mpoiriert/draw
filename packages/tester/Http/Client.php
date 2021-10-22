<?php

namespace Draw\Component\Tester\Http;

use Draw\Component\Tester\Http\Cookie\CookieClientObserver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;

class Client implements ClientInterface
{
    private $requestFactory;

    private $requestExecutioner;

    /**
     * @var ClientObserver[]
     */
    private $observers = [];

    public function __construct(
        RequestExecutionerInterface $requestExecutioner = null,
        RequestFactoryInterface $requestFactory = null
    ) {
        $this->requestExecutioner = $requestExecutioner ?: new CurlRequestExecutioner();
        $this->requestFactory = $requestFactory ?: new RequestFactory();
        $this->registerObserver(new CookieClientObserver(), 200);
    }

    public function getRequestExecutioner(): RequestExecutionerInterface
    {
        return $this->requestExecutioner;
    }

    public function setRequestExecutioner(RequestExecutionerInterface $requestExecutioner): void
    {
        $this->requestExecutioner = $requestExecutioner;
    }

    public function registerObserver(ClientObserver $observer, $position = 0): void
    {
        $this->observers[$position][] = $observer;
    }

    /**
     * @param $uri
     * @param string $version
     */
    public function get($uri, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('GET', $uri, null, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string $version
     */
    public function head($uri, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('HEAD', $uri, null, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function put($uri, $body, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('PUT', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function post($uri, $body, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('POST', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function patch($uri, $body, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('PATCH', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string $version
     */
    public function delete($uri, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('DELETE', $uri, null, $headers, $version)
        );
    }

    /**
     * @param $uri
     * @param string $version
     */
    public function options($uri, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('OPTIONS', $uri, null, $headers, $version)
        );
    }

    /**
     * @throws Throwable
     */
    public function send(RequestInterface $request): TestResponse
    {
        $observers = $this->observers;
        ksort($observers);

        /** @var ClientObserver[] $observers */
        $observers = array_merge(...$observers);

        foreach ($observers as $observer) {
            $request = $observer->preSendRequest($request);
        }

        foreach ($observers as $observer) {
            $observer->preExecute($request, $this->requestExecutioner);
        }

        $e = null;
        try {
            $response = $this->requestExecutioner->executeRequest($request);
        } catch (Throwable $exception) {
            foreach ($observers as $observer) {
                $observer->postExecutionError($request, $exception, $this->requestExecutioner);
            }

            throw $exception;
        }

        foreach ($observers as $observer) {
            $observer->postExecute($request, $response, $this->requestExecutioner);
        }

        foreach ($observers as $observer) {
            $response = $observer->postSendRequest($request, $response);
        }

        return new TestResponse($request, $response);
    }

    /**
     * @param $method
     * @param $uri
     * @param string|resource|StreamInterface|null $body
     * @param string                               $version
     */
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1'): RequestInterface
    {
        return $this->requestFactory->createRequest(...func_get_args());
    }
}
