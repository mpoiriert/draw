<?php

namespace Draw\Component\Tester\Http\Request;

use Psr\Http\Message\RequestInterface;

class SuperGlobalsExtractor
{
    private BodyParser $bodyParser;

    private $requestOrder;

    public function __construct(?BodyParser $bodyParser = null, $requestOrder = null)
    {
        $this->bodyParser = $bodyParser ?: new BodyParser();
        $this->requestOrder = $requestOrder ?: \ini_get('variables_order');
    }

    /**
     * @return array{_SERVER: mixed, _POST: mixed, _GET: mixed, _COOKIE: mixed, _FILES: mixed, _REQUEST: mixed}
     */
    public function extractSuperGlobals(RequestInterface $request): array
    {
        $body = $request->getBody();
        $body->rewind();
        $content = $body->getContents();
        $body->rewind();
        $result = $this->bodyParser->parse($content, $request->getHeaderLine('Content-Type'));

        return [
            '_SERVER' => $this->extractServerData($request),
            '_POST' => $post = $result['post'],
            '_GET' => $get = $this->extractGetData($request),
            '_COOKIE' => $cookie = $this->extractCookieData($request),
            '_FILES' => $result['files'],
            '_REQUEST' => $this->mergeDataForRequest($get, $post, $cookie),
        ];
    }

    /**
     * @param array{_SERVER: array<string,mixed>, _POST: array<string,mixed>, _GET: array<string,mixed>, _COOKIE: array<string,mixed>, _FILES: array<string,mixed>, _REQUEST: array<string,mixed>} $extractedSuperGlobals
     *
     * @return array{_SERVER: array<string,mixed>, _POST: array<string,mixed>, _GET: array<string,mixed>, _COOKIE: array<string,mixed>, _FILES: array<string,mixed>, _REQUEST: array<string,mixed>}
     */
    public function assignSuperGlobals(array $extractedSuperGlobals): array
    {
        $previousSuperGlobals = [
            '_SERVER' => $_SERVER,
            '_POST' => $_POST,
            '_GET' => $_GET,
            '_COOKIE' => $_COOKIE,
            '_FILES' => $_FILES,
            '_REQUEST' => $_REQUEST,
        ];

        $_SERVER = $extractedSuperGlobals['_SERVER'];
        $_POST = $extractedSuperGlobals['_POST'];
        $_GET = $extractedSuperGlobals['_GET'];
        $_COOKIE = $extractedSuperGlobals['_COOKIE'];
        $_FILES = $extractedSuperGlobals['_FILES'];
        $_REQUEST = $extractedSuperGlobals['_REQUEST'];

        return $previousSuperGlobals;
    }

    /**
     * @return array<string, string>
     */
    public function extractCookieData(RequestInterface $request): array
    {
        $cookie = [];
        foreach ($request->getHeader('Cookie') as $cookieLine) {
            foreach (explode(';', $cookieLine) as $oneCookie) {
                if (!($oneCookie = trim($oneCookie))) {
                    continue;
                }

                if (false === strpos($oneCookie, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $oneCookie);
                $cookie[$name] = $value;
            }
        }

        return $cookie;
    }

    /**
     * @param array<string, mixed>  $post
     * @param array<string, string> $cookie
     * @param array<string, mixed>  $get
     *
     * @return array<string, mixed>
     */
    public function mergeDataForRequest(array $get, array $post, array $cookie): array
    {
        $requestOrder = preg_replace('#[^cgp]#', '', strtolower($this->requestOrder)) ?: 'gp';
        $data = ['g' => $get, 'p' => $post, 'c' => $cookie];

        $request = [];
        foreach (str_split($requestOrder) as $order) {
            $request = array_merge($request, $data[$order]);
        }

        return $request;
    }

    public function extractGetData(RequestInterface $request): array
    {
        $get = [];
        parse_str($request->getUri()->getQuery(), $get);

        return $get;
    }

    /**
     * @return array<string, string>
     */
    private function extractServerData(RequestInterface $request): array
    {
        $server = [];
        foreach ($request->getHeaders() as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $server[$key] = implode(', ', $value);
            } else {
                $server['HTTP_'.$key] = implode(', ', $value);
            }
        }

        $server['QUERY_STRING'] = $request->getUri()->getQuery();
        $server['HTTP_HOST'] = $request->getUri()->getHost();
        $server['REQUEST_URI'] = $request->getUri()->getPath();
        $server['REQUEST_METHOD'] = $request->getMethod();

        return $server;
    }
}
