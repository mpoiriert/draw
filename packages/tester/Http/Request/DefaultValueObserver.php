<?php

namespace Draw\Component\Tester\Http\Request;

use Draw\Component\Tester\Http\ClientObserver;
use Psr\Http\Message\RequestInterface;

class DefaultValueObserver extends ClientObserver
{
    public function __construct(private array $headers = [], private $queryParameters = [])
    {
    }

    public function preSendRequest(RequestInterface $request): RequestInterface
    {
        $request = $this->withDefaultHeaders($request);

        return $this->withDefaultQueryParameters($request);
    }

    public function withDefaultQueryParameters(RequestInterface $request): RequestInterface
    {
        if ($this->queryParameters) {
            $uri = $request->getUri();
            $query = http_build_query($this->queryParameters).'&'.$uri->getQuery();
            $uri = $uri->withQuery(rtrim($query, '&'));
            $request = $request->withUri($uri);
        }

        return $request;
    }

    public function withDefaultHeaders(RequestInterface $request): RequestInterface
    {
        foreach ($this->headers as $name => $value) {
            if ($request->hasHeader($name)) {
                continue;
            }

            $request = $request->withAddedHeader($name, $value);
        }

        return $request;
    }
}
