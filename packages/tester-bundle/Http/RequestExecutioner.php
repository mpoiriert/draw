<?php

namespace Draw\Bundle\TesterBundle\Http;

use Draw\Component\Tester\Http\Request\BodyParser;
use Draw\Component\Tester\Http\RequestExecutionerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Cookie;

class RequestExecutioner implements RequestExecutionerInterface
{
    private BodyParser $bodyParser;

    private ?AbstractBrowser $lastBrowser = null;

    public function __construct(
        private BrowserFactoryInterface $browserFactory,
        ?BodyParser $bodyParser = null
    ) {
        $this->bodyParser = $bodyParser ?: new BodyParser();
    }

    public function executeRequest(RequestInterface $request): ResponseInterface
    {
        $content = $request->getBody()->getContents();

        $contentTypes = $request->getHeader('Content-Type');

        $parsedBody = $this->bodyParser->parse($content, $contentTypes ? $contentTypes[0] : null);

        $response = $this->doExecuteRequest(
            $request->getMethod(),
            (string) $request->getUri(),
            $parsedBody['post'],
            $parsedBody['files'],
            $this->extractServerData($request),
            $content,
            true
        );

        return new Response(
            $response->getStatusCode(),
            $response->headers->all(),
            $response->getContent(),
            $response->getProtocolVersion()
        );
    }

    protected function doExecuteRequest(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true
    ): \Symfony\Component\HttpFoundation\Response {
        $this->lastBrowser = $browser = $this->browserFactory->createBrowser();

        $cookies = array_filter(explode(';', $server['HTTP_COOKIE'] ?? ''));

        foreach ($cookies as $cookie) {
            [$name, $value] = explode('=', $cookie);
            $this->lastBrowser->getCookieJar()
                ->set(new Cookie($name, $value));
        }

        $browser->request(...\func_get_args());
        /* @var $response \Symfony\Component\HttpFoundation\Response */
        $response = $browser->getResponse();

        return $response;
    }

    public function getLastBrowser(): ?AbstractBrowser
    {
        return $this->lastBrowser;
    }

    /**
     * @return array<string,string>
     */
    private function extractServerData(RequestInterface $request): array
    {
        $server = [];
        foreach ($request->getHeaders() as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'REMOTE_ADDR'])) {
                $server[$key] = implode(', ', $value);
            } else {
                $server['HTTP_'.$key] = implode(', ', $value);
            }
        }

        return $server;
    }
}
