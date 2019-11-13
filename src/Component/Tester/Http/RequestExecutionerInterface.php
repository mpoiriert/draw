<?php namespace Draw\Component\Tester\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestExecutionerInterface
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function executeRequest(RequestInterface $request): ResponseInterface;
}