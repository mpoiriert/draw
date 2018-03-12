<?php

namespace Draw\HttpTester\Request;

use Draw\HttpTester\ClientObserver;
use Draw\HttpTester\RequestExecutionerInterface;
use Exception;
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

    public function preExecute(RequestInterface $request, RequestExecutionerInterface $requestExecutioner)
    {
        $superGlobals = $this->superGlobalExtractors->extractSuperGlobals($request);
        $this->previousSuperGlobal = $this->superGlobalExtractors->assignSuperGlobals($superGlobals);
    }

    public function postExecute(
        RequestInterface $request,
        ResponseInterface $response,
        RequestExecutionerInterface $requestExecutioner
    ) {
        $this->superGlobalExtractors->assignSuperGlobals($this->previousSuperGlobal);
        $this->previousSuperGlobal = [];
    }

    public function postExecutionError(
        RequestInterface $request,
        Exception $exception,
        RequestExecutionerInterface $requestExecutioner
    ) {
        $this->superGlobalExtractors->assignSuperGlobals($this->previousSuperGlobal);
        $this->previousSuperGlobal = [];
    }
}