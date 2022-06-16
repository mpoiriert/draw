<?php

namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ClientObserver
{
    public function preSendRequest(RequestInterface $request): RequestInterface
    {
        return $request;
    }

    public function postSendRequest(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    public function preExecute(RequestInterface $request, RequestExecutionerInterface $requestExecutioner): void
    {
    }

    public function postExecute(
        RequestInterface $request,
        ResponseInterface $response,
        RequestExecutionerInterface $requestExecutioner
    ): void {
    }

    public function postExecutionError(
        RequestInterface $request,
        \Throwable $throwable,
        RequestExecutionerInterface $requestExecutioner
    ): void {
    }
}
