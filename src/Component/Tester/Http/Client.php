<?php namespace Draw\Component\Tester\Http;

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
        $this->registerObserver(new CookieClientObserver());
    }

    public function getRequestExecutioner(): RequestExecutionerInterface
    {
        return $this->requestExecutioner;
    }

    /**
     * @param RequestExecutionerInterface $requestExecutioner
     */
    public function setRequestExecutioner(RequestExecutionerInterface $requestExecutioner): void
    {
        $this->requestExecutioner = $requestExecutioner;
    }

    public function registerObserver(ClientObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    /**
     * @param $uri
     * @param array $headers
     * @param string $version
     * @return TestResponse
     */
    public function get($uri, array $headers = [], $version = '1.1'): TestResponse
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
    public function head($uri, array $headers = [], $version = '1.1'): TestResponse
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
    public function put($uri, $body, array $headers = [], $version = '1.1'): TestResponse
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
    public function post($uri, $body, array $headers = [], $version = '1.1'): TestResponse
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
    public function patch($uri, $body, array $headers = [], $version = '1.1'): TestResponse
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
    public function delete($uri, array $headers = [], $version = '1.1'): TestResponse
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
    public function options($uri, array $headers = [], $version = '1.1'): TestResponse
    {
        return $this->send(
            $this->createRequest('OPTIONS', $uri, null, $headers, $version)
        );
    }

    /**
     * @param RequestInterface $request
     * @return TestResponse
     * @throws Throwable
     */
    public function send(RequestInterface $request): TestResponse
    {
        foreach ($this->observers as $observer) {
            $request = $observer->preSendRequest($request);
        }

        foreach ($this->observers as $observer) {
            $observer->preExecute($request, $this->requestExecutioner);
        }

        $e = null;
        try {
            $response = $this->requestExecutioner->executeRequest($request);
        } catch (Throwable $exception) {
            foreach ($this->observers as $observer) {
                $observer->postExecutionError($request, $exception, $this->requestExecutioner);
            }

            throw $exception;
        }

        foreach ($this->observers as $observer) {
            $observer->postExecute($request, $response, $this->requestExecutioner);
        }

        foreach ($this->observers as $observer) {
            $response = $observer->postSendRequest($request, $response);
        }

        return new TestResponse($request, $response);
    }

    /**
     * @param $method
     * @param $uri
     * @param string|null|resource|StreamInterface $body
     * @param array $headers
     * @param string $version
     * @return RequestInterface
     */
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1'): RequestInterface
    {
        return $this->requestFactory->createRequest(...func_get_args());
    }
}