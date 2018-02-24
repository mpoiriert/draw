<?php

namespace Draw\HttpTester;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestFactoryTest extends TestCase
{
    public function testConstruct()
    {
        $requestFactory = new RequestFactory();
        $this->assertInstanceOf(RequestFactoryInterface::class, $requestFactory);

        return $requestFactory;
    }

    /**
     * @depends testConstruct
     *
     * @param RequestFactory $requestFactory
     */
    public function testCreateRequest(RequestFactory $requestFactory)
    {
        $request = $requestFactory->createRequest(
            $method = 'POST',
            $uri = '/test',
            $body = 'body',
            $headers = ['header' => 'value'],
            $version = '1.0'
        );

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