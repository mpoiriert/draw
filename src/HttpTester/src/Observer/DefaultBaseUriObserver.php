<?php

namespace Draw\HttpTester\Observer;

use Draw\HttpTester\ClientObserver;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

class DefaultBaseUriObserver extends ClientObserver
{
    private $baseUri;

    public function __construct($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    public function preSendRequest(RequestInterface $request)
    {
        $uri = $request->getUri();
        if(empty($uri->getHost())) {
            $request = $request->withUri(
                new Uri($this->baseUri . $request->getUri())
            );
        }

        return $request;
    }
}
