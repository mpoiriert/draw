<?php

namespace Draw\HttpTester\Request;

use Psr\Http\Message\RequestInterface;

class SuperGlobalsExtractor
{
    /**
     * @var BodyParser
     */
    private $bodyParser;

    private $requestOrder;

    public function __construct(BodyParser $bodyParser = null, $requestOrder = null)
    {
        $this->bodyParser = $bodyParser ?: new BodyParser();

        $this->requestOrder = ini_get('request_order') ?: ini_get('variables_order');
    }

    public function extractSuperGlobals(RequestInterface $request)
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
            '_REQUEST' => $this->mergeDataForRequest($get, $post, $cookie)
        ];
    }

    public function assignSuperGlobals($extractedSuperGlobals)
    {
        $previousSuperGlobals = [
            '_SERVER' => $_SERVER,
            '_POST' => $_POST,
            '_GET' => $_GET,
            '_COOKIE' => $_COOKIE,
            '_FILES' => $_FILES,
            '_REQUEST' => $_REQUEST
        ];

        $_SERVER = $extractedSuperGlobals['_SERVER'];
        $_POST = $extractedSuperGlobals['_POST'];
        $_GET = $extractedSuperGlobals['_GET'];
        $_COOKIE = $extractedSuperGlobals['_COOKIE'];
        $_FILES = $extractedSuperGlobals['_FILES'];
        $_REQUEST = $extractedSuperGlobals['_REQUEST'];

        return $previousSuperGlobals;
    }

    public function extractCookieData(RequestInterface $request)
    {
        $cookie = [];
        foreach ($request->getHeader('Cookie') as $cookieLine) {
            foreach (explode(';', $cookieLine) as $oneCookie) {
                if (!($oneCookie = trim($oneCookie))) {
                    continue;
                }

                if (strpos($oneCookie, '=') === false) {
                    continue;
                }

                list($name, $value) = explode('=', $oneCookie);
                $cookie[$name] = $value;
            }
        }

        return $cookie;
    }

    public function mergeDataForRequest($get, $post, $cookie)
    {
        $requestOrder = preg_replace('#[^cgp]#', '', strtolower($this->requestOrder)) ?: 'gp';
        $data = ['g' => $get, 'p' => $post, 'c' => $cookie];

        $request = [];
        foreach (str_split($requestOrder) as $order) {
            $request = array_merge($request, $data[$order]);
        }

        return $request;
    }

    public function extractGetData(RequestInterface $request)
    {
        $get = [];
        parse_str($request->getUri()->getQuery(), $get);
        return $get;
    }

    private function extractServerData(RequestInterface $request)
    {
        $server = [];
        foreach ($request->getHeaders() as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            if (in_array($key, array('CONTENT_TYPE', 'CONTENT_LENGTH'))) {
                $server[$key] = implode(', ', $value);
            } else {
                $server['HTTP_' . $key] = implode(', ', $value);
            }
        }

        $server['QUERY_STRING'] = $request->getUri()->getQuery();
        $server['HTTP_HOST'] = $request->getUri()->getHost();
        $server['REQUEST_URI'] = $request->getUri()->getPath();
        $server['REQUEST_METHOD'] = $request->getMethod();

        return $server;
    }
}