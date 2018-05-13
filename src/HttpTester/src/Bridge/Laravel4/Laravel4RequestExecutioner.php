<?php

namespace Draw\HttpTester\Bridge\Laravel4;

use Draw\HttpTester\RequestExecutionerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class Laravel4RequestExecutioner implements RequestExecutionerInterface
{
    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    private $app;

    /**
     * The HttpKernel client instance.
     *
     * @var \Illuminate\Foundation\Testing\Client
     */
    private $client;

    /**
     * @var Laravel4TestContextInterface
     */
    private $context;

    public function __construct(Laravel4TestContextInterface $context)
    {
        $this->context = $context;
    }

    private function refreshApplication()
    {
        if(!is_null($this->app)) {
            $this->app->shutdown();
        }

        $this->app = $this->context->createApplication();

        $this->client = new \Illuminate\Foundation\Testing\Client($this->app);

        $this->app->setRequestForConsoleEnvironment();

        $this->app->boot();

        // We re-enable the filters since laravel disable them
        // We want a full integration testing, not just the controller
        $this->app['router']->enableFilters();
    }

    public function executeRequest(RequestInterface $request)
    {
        $this->refreshApplication();

        $this->client->request(
            $request->getMethod(),
            (string)$request->getUri(),
            [],
            [],
            $this->extractServerData($request),
            $request->getBody()->getContents(),
            true
        );

        $response = $this->client->getResponse();

        return new Response(
            $response->getStatusCode(),
            $response->headers->all(),
            $response->getContent(),
            $response->getProtocolVersion()
        );
    }

    private function extractServerData(RequestInterface $request)
    {
        $server = [];
        foreach ($request->getHeaders() as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (in_array($key, array('CONTENT_TYPE', 'CONTENT_LENGTH'))) {
                $server[$key] = implode(', ', $value);
            } else {
                $server['HTTP_'.$key] = implode(', ', $value);
            }
        }
        return $server;
    }
}