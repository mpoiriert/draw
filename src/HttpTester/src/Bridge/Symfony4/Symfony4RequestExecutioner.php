<?php

namespace Draw\HttpTester\Bridge\Symfony4;

use Draw\HttpTester\Request\BodyParser;
use Draw\HttpTester\RequestExecutionerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class Symfony4RequestExecutioner implements RequestExecutionerInterface
{
    /**
     * @var BodyParser
     */
    private $bodyParser;

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
        $this->bodyParser = new BodyParser();
    }

    private function refreshApplication()
    {
        $this->client = $this->context->getTestClient();
    }

    public function executeRequest(RequestInterface $request)
    {
        $this->refreshApplication();

        $content = $request->getBody()->getContents();

        $contentTypes = $request->getHeader('Content-Type');

        $parsedBody = $this->bodyParser->parse($content, $contentTypes ? $contentTypes[0] : null);

        $this->client->request(
            $request->getMethod(),
            (string)$request->getUri(),
            $parsedBody['post'],
            $parsedBody['files'],
            $this->extractServerData($request),
            $content,
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