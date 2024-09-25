<?php

namespace Draw\Component\Tester\Tests\Http;

use Draw\Component\Tester\Http\RequestFactory;
use Draw\Component\Tester\Http\RequestFactoryInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestFactoryTest extends TestCase
{
    public function testConstruct(): RequestFactory
    {
        $requestFactory = new RequestFactory();
        static::assertInstanceOf(RequestFactoryInterface::class, $requestFactory);

        return $requestFactory;
    }

    #[Depends('testConstruct')]
    public function testCreateRequest(RequestFactory $requestFactory): void
    {
        $request = $requestFactory->createRequest(
            $method = 'POST',
            $uri = '/test',
            $body = 'body',
            $headers = ['header' => 'value'],
            $version = '1.0'
        );

        static::assertInstanceOf(RequestInterface::class, $request);

        static::assertSame($method, $request->getMethod());
        static::assertSame($uri, $request->getUri()->__toString());
        static::assertSame($body, $request->getBody()->getContents());

        foreach ($headers as $key => $value) {
            static::assertContains($value, $request->getHeader($key));
        }

        static::assertSame($version, $request->getProtocolVersion());
    }
}
