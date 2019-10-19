<?php namespace Draw\Component\Tester\Http\Request;

use Draw\Component\Tester\Http\ClientObserver;
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
    public function preSendRequest(RequestInterface $request): RequestInterface
    {
        $request = $this->withDefaultHeaders($request);
        $request = $this->withDefaultQueryParameters($request);

        return $request;
    }

    public function withDefaultQueryParameters(RequestInterface $request)
    {
        if($this->queryParameters) {
            $uri = $request->getUri();
            $query = http_build_query($this->queryParameters) . '&' . $uri->getQuery();
            $uri = $uri->withQuery(rtrim($query, '&'));
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