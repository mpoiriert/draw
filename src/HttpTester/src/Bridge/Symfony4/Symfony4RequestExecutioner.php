<?php

namespace Draw\HttpTester\Bridge\Symfony4;

use Draw\HttpTester\RequestExecutionerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class Symfony4RequestExecutioner implements RequestExecutionerInterface
{
    /**
     * @var Symfony4TestContextInterface
     */
    private $context;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    public function __construct(Symfony4TestContextInterface $context)
    {
        $this->context = $context;
    }

    private function refreshApplication()
    {
        $this->client = $this->context->getTestClient();
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