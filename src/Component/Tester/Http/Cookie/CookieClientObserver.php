<?php

namespace Draw\Component\Tester\Http\Cookie;

use Draw\Component\Tester\Http\ClientObserver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieClientObserver extends ClientObserver
{
    private $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar();
    }

    public function preSendRequest(RequestInterface $request): RequestInterface
    {
        return $this->cookieJar->withCookieHeader($request);
    }

    public function postSendRequest(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->cookieJar->extractCookies($request, $response);

        return $response;
    }
}
