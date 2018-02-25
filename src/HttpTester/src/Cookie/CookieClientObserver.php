<?php

namespace Draw\HttpTester\Cookie;

use Draw\HttpTester\ClientObserver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieClientObserver extends ClientObserver
{
    private $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar();
    }

    public function preSendRequest(RequestInterface $request)
    {
        return $this->cookieJar->withCookieHeader($request);
    }

    public function postSendRequest(RequestInterface $request, ResponseInterface $response)
    {
        $this->cookieJar->extractCookies($request, $response);
        return $response;
    }
}