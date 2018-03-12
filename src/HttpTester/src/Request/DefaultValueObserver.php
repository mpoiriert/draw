<?php

namespace Draw\HttpTester\Request;

use Draw\HttpTester\ClientObserver;
use Psr\Http\Message\RequestInterface;

class DefaultValueObserver extends ClientObserver
{
    private $headers;

    private $queryParameters;

    public function __construct(array $headers = [], $queryParameters = [])
    {
        $this->headers = $headers;
        $this->queryParameters = $queryParameters;
    }

    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function preSendRequest(RequestInterface $request)
    {
        $request = $this->withDefaultHeaders($request);
        $request = $this->withDefaultQueryParameters($request);

        return $request;
    }

    public function withDefaultQueryParameters(RequestInterface $request)
    {
        if($this->queryParameters) {
            $uri = $request->getUri();
            $query = $uri->getQuery() . '&'. http_build_query($this->queryParameters);
            $uri = $uri->withQuery(ltrim('&', $query));
            $request = $request->withUri($uri);
        }

        return $request;
    }

    public function withDefaultHeaders(RequestInterface $request)
    {
        foreach($this->headers as $name => $value) {
            if($request->hasHeader($name)) {
                continue;
            }

            $request = $request->withAddedHeader($name, $value);
        }

        return $request;
    }
}