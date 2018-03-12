<?php

namespace Draw\HttpTester;

use Exception;
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

    public function preExecute(RequestInterface $request, RequestExecutionerInterface $requestExecutioner)
    {

    }

    public function postExecute(
        RequestInterface $request,
        ResponseInterface $response,
        RequestExecutionerInterface $requestExecutioner
    ) {

    }

    public function postExecutionError(
        RequestInterface $request,
        Exception $exception,
        RequestExecutionerInterface $requestExecutioner
    ) {

    }
}