<?php

namespace Draw\Component\Tester\Http\Request;

use Draw\Component\Tester\Http\ClientObserver;
use Draw\Component\Tester\Http\RequestExecutionerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ExtractSuperGlobalObserver extends ClientObserver
{
    /**
     * @var SuperGlobalsExtractor
     */
    private $superGlobalExtractors;

    private $previousSuperGlobal;

    public function __construct(SuperGlobalsExtractor $superGlobalsExtractor = null)
    {
        $this->superGlobalExtractors = $superGlobalsExtractor ?: new SuperGlobalsExtractor();
    }

    public function preExecute(RequestInterface $request, RequestExecutionerInterface $requestExecutioner): void
    {
        $superGlobals = $this->superGlobalExtractors->extractSuperGlobals($request);
        $this->previousSuperGlobal = $this->superGlobalExtractors->assignSuperGlobals($superGlobals);
    }

    public function postExecute(
        RequestInterface $request,
        ResponseInterface $response,
        RequestExecutionerInterface $requestExecutioner
    ): void {
        $this->superGlobalExtractors->assignSuperGlobals($this->previousSuperGlobal);
        $this->previousSuperGlobal = [];
    }

    public function postExecutionError(
        RequestInterface $request,
        \Throwable $throwable,
        RequestExecutionerInterface $requestExecutioner
    ): void {
        $this->superGlobalExtractors->assignSuperGlobals($this->previousSuperGlobal);
        $this->previousSuperGlobal = [];
    }
}
