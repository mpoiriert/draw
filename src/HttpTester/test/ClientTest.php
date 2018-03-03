<?php

namespace Draw\HttpTester;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase
{
    public function testConstruct()
    {
        $requestExecutioner = $this->getMockBuilder(RequestExecutionerInterface::class)
            ->setMethods(['executeRequest'])
            ->getMock();

        $requestExecutioner->method('executeRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'method' => $request->getMethod(),
                            'uri' => $request->getUri()->__toString(),
                            'body' => $request->getBody()->getContents(),
                            'headers' => $request->getHeaders(),
                            'version' => $request->getProtocolVersion()
                        ]
                    )
                );
            });

        $client = new Client($requestExecutioner);

        $this->assertInstanceOf(ClientInterface::class, $client);

        return $client;
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testGet(Client $client)
    {
        $testResponse = $client->get(
            $uri = '/test',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'GET',
            $uri,
            null,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testHead(Client $client)
    {
        $testResponse = $client->head(
            $uri = '/test',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'HEAD',
            $uri,
            null,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testPut(Client $client)
    {
        $testResponse = $client->put(
            $uri = '/test',
            $body = 'body',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'PUT',
            $uri,
            $body,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testPost(Client $client)
    {
        $testResponse = $client->post(
            $uri = '/test',
            $body = 'body',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'POST',
            $uri,
            $body,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testDelete(Client $client)
    {
        $testResponse = $client->delete(
            $uri = '/test',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'DELETE',
            $uri,
            null,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testOptions(Client $client)
    {
        $testResponse = $client->options(
            $uri = '/test',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'OPTIONS',
            $uri,
            null,
            $headers,
            $version
        );
    }


    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testPatch(Client $client)
    {
        $testResponse = $client->patch(
            $uri = '/test',
            $body = 'body',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidTestResponse(
            $testResponse,
            'PATCH',
            $uri,
            $body,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testSend(Client $client)
    {
        $request = new Request(
            $method = 'POST',
            $uri = '/test',
            $headers = ['header' => ['value']],
            $body = 'body',
            $version = '1.0'
        );

        $testResponse = $client->send($request);

        $this->assertValidTestResponse(
            $testResponse,
            $method,
            $uri,
            $body,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testCreateRequest(Client $client)
    {
        $request = $client->createRequest(
            $method = 'POST',
            $uri = '/test',
            $body = 'body',
            $headers = ['header' => ['value']],
            $version = '1.0'
        );

        $this->assertValidRequest(
            $request,
            $method,
            $uri,
            $body,
            $headers,
            $version
        );
    }

    /**
     * @depends testConstruct
     *
     * @param Client $client
     */
    public function testRegisterObserver(Client $client)
    {
        $mockClientObserver = $this->getMockBuilder(ClientObserver::class)
            ->setMethodsExcept([])
            ->getMockForAbstractClass();

        $mockClientObserver
            ->expects($this->once())
            ->method('preSendRequest')
            ->willReturnCallback(function(RequestInterface $request) {
                return $request;
            });

        $mockClientObserver
            ->expects($this->once())
            ->method('postSendRequest')
            ->willReturnCallback(function(RequestInterface $request, ResponseInterface $response) {
                return $response;
            });

        /** @var ClientObserver $mockClientObserver */
        $client->registerObserver($mockClientObserver);

        $client->send(new Request('GET', '/test'));
    }

    public function assertValidTestResponse(
        TestResponse $testResponse,
        $method,
        $uri,
        $body = null,
        array $headers = [],
        $version = '1.1'
    ) {
        $body = $body ?: '';
        $response = $testResponse->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);

        //We seek at the beginning of the body to be sure that nobody change the position before
        $response->getBody()->seek(0);

        $this->assertJsonStringEqualsJsonString(
            json_encode(compact('method', 'uri', 'body', 'headers', 'version')),
            $response->getBody()->getContents()
        );

        $this->assertValidRequest(
            $testResponse->getRequest(),
            $method,
            $uri,
            $body,
            $headers,
            $version
        );
    }

    public function assertValidRequest(
        RequestInterface $request,
        $method,
        $uri,
        $body = null,
        array $headers = [],
        $version = '1.1'
    ) {
        //We seek at the beginning of the body to be sure that nobody change the position before
        $request->getBody()->seek(0);

        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, $request->getUri()->__toString());
        $this->assertSame($body ?: '', $request->getBody()->getContents());

        foreach ($headers as $key => $values) {
            $this->assertSame($values, $request->getHeader($key));
        }

        $this->assertSame($version, $request->getProtocolVersion());
    }
}