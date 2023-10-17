<?php

namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientObserverInterface
{
    public function preSendRequest(RequestInterface $request): RequestInterface;

    public function postSendRequest(RequestInterface $request, ResponseInterface $response): ResponseInterface;

    public function preExecute(RequestInterface $request, RequestExecutionerInterface $requestExecutioner): void;

    public function postExecute(
        RequestInterface $request,
        ResponseInterface $response,
        RequestExecutionerInterface $requestExecutioner
    );

    public function postExecutionError(
        RequestInterface $request,
        \Throwable $throwable,
        RequestExecutionerInterface $requestExecutioner
    );
}
