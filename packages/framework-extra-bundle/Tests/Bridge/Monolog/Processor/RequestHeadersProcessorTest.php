<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\Bridge\Monolog\Processor;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestHeadersProcessorTest extends TestCase
{
    public static function provideTestInvoke(): iterable
    {
        $requestHeaders = [
            $header1 = uniqid('header1-') => [
                uniqid('header1-value1-'),
                uniqid('header1-value2-'),
            ],
            $header2 = uniqid('header2-') => [
                uniqid('header2-value1-'),
            ],
        ];

        yield 'all' => [
            $requestHeaders,
            [],
            [],
            $requestHeaders,
        ];

        yield 'no-request' => [
            null,
            [],
            [],
            null,
        ];

        yield 'only' => [
            $requestHeaders,
            [$header2],
            [],
            [$header2 => $requestHeaders[$header2]],
        ];

        yield 'ignore' => [
            $requestHeaders,
            [],
            [$header2],
            [$header1 => $requestHeaders[$header1]],
        ];

        yield 'only-and-ignore' => [
            $requestHeaders,
            [$header1],
            [$header2],
            [$header1 => $requestHeaders[$header1]],
        ];

        yield 'only-and-ignore-exclude-all' => [
            $requestHeaders,
            [$header2],
            [$header2],
            [],
        ];
    }

    /**
     * @dataProvider provideTestInvoke
     */
    public function testInvoke(
        ?array $requestHeaders,
        array $onlyHeaders,
        array $ignoreHeader,
        ?array $expectedHeaders
    ): void {
        $service = new RequestHeadersProcessor(
            $requestStack = $this->createMock(RequestStack::class),
            $onlyHeaders,
            $ignoreHeader,
            $key = uniqid('key-')
        );

        if (null === $requestHeaders) {
            $requestStack
                ->expects(static::once())
                ->method('getMainRequest')
                ->willReturn(null);
        } else {
            $requestStack
                ->expects(static::once())
                ->method('getMainRequest')
                ->willReturn($mainRequest = new Request());

            $mainRequest->headers->replace($requestHeaders);
        }

        $originalRecords = [uniqid('header-name-') => uniqid()];
        $records = $service->__invoke($originalRecords);

        if (null === $expectedHeaders) {
            static::assertSame(
                $originalRecords,
                $records
            );

            return;
        }

        static::assertSame(
            array_merge(
                $originalRecords,
                ['extra' => [$key => $expectedHeaders]]
            ),
            $records
        );
    }

    public function testInvokeNoRequestStack(): void
    {
        $service = new RequestHeadersProcessor(
            null,
            [],
            [],
            uniqid()
        );

        $records = [uniqid() => uniqid()];

        static::assertSame(
            $records,
            $service->__invoke($records)
        );
    }
}
