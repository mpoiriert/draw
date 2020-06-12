<?php

namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestExecutionerInterface
{
    public function executeRequest(RequestInterface $request): ResponseInterface;
}
