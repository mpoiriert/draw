<?php

namespace Draw\Component\Tester\Http\Observer;

use Draw\Component\Tester\Http\ClientObserver;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

class DefaultBaseUriObserver extends ClientObserver
{
    public function __construct(private $baseUri)
    {
    }

    public function preSendRequest(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();
        if (empty($uri->getHost())) {
            $request = $request->withUri(
                new Uri($this->baseUri.$request->getUri())
            );
        }

        return $request;
    }
}
