<?php

namespace Draw\Component\Tester\Http;

use Draw\Component\Tester\Http\Cookie\CookieClientObserver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class Client implements ClientInterface
{
    private RequestFactoryInterface $requestFactory;

    private RequestExecutionerInterface $requestExecutioner;

    /**
     * @var array<array<ClientObserverInterface>>
     */
    private array $clientObservers = [];

    public function __construct(
        ?RequestExecutionerInterface $requestExecutioner = null,
        ?RequestFactoryInterface $requestFactory = null
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

    public function registerObserver(ClientObserverInterface $clientObserver, int $position = 0): void
    {
        $this->clientObservers[$position][] = $clientObserver;
    }

    public function get(string $uri, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('GET', $uri, null, $headers, $version)
        );
    }

    public function head(string $uri, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('HEAD', $uri, null, $headers, $version)
        );
    }

    /**
     * @param string|resource|StreamInterface|null $body
     */
    public function put(string $uri, $body, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('PUT', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param string|resource|StreamInterface|null $body
     */
    public function post(string $uri, $body, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('POST', $uri, $body, $headers, $version)
        );
    }

    /**
     * @param string|resource|StreamInterface|null $body
     */
    public function patch(string $uri, $body, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('PATCH', $uri, $body, $headers, $version)
        );
    }

    public function delete(string $uri, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('DELETE', $uri, null, $headers, $version)
        );
    }

    public function options(string $uri, array $headers = [], string $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('OPTIONS', $uri, null, $headers, $version)
        );
    }

    /**
     * @throws \Throwable
     */
    public function send(RequestInterface $request): TestResponse
    {
        $observers = $this->clientObservers;
        ksort($observers);

        $observers = array_merge(...$observers);

        foreach ($observers as $observer) {
            $request = $observer->preSendRequest($request);
        }

        foreach ($observers as $observer) {
            $observer->preExecute($request, $this->requestExecutioner);
        }

        try {
            $response = $this->requestExecutioner->executeRequest($request);
        } catch (\Throwable $exception) {
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
     * @param string|resource|StreamInterface|null $body
     */
    public function createRequest(string $method, string $uri, $body = null, array $headers = [], string $version = '1.1'): RequestInterface
    {
        return $this->requestFactory->createRequest(...\func_get_args());
    }
}
