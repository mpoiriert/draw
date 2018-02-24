<?php

namespace Draw\HttpTester;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class ClientTest extends TestCase
{
    public function testConstruct()
    {
        $client = new Client();

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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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
            $headers = ['header' => 'value'],
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

    public function assertValidTestResponse(
        TestResponse $testResponse,
        $method,
        $uri,
        $body = null,
        array $headers = [],
        $version = '1.1'
    ) {
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
        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, $request->getUri()->__toString());
        $this->assertSame($body ?: '', $request->getBody()->getContents());

        foreach ($headers as $key => $value) {
            $this->assertContains($value, $request->getHeader($key));
        }

        $this->assertSame($version, $request->getProtocolVersion());
    }
}