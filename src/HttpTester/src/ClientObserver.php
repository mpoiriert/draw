<?php

namespace Draw\HttpTester;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ClientObserver
{
    public function preSendRequest(RequestInterface $request)
    {
        return $request;
    }

    public function postSendRequest(RequestInterface $request, ResponseInterface $response)
    {
        return $response;
    }
}