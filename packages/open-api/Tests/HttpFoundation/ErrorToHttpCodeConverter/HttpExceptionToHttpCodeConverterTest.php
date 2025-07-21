<?php

namespace Draw\Component\OpenApi\Tests\HttpFoundation\ErrorToHttpCodeConverter;

use Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter\ErrorToHttpCodeConverterInterface;
use Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter\HttpExceptionToHttpCodeConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @internal
 */
class HttpExceptionToHttpCodeConverterTest extends TestCase
{
    private HttpExceptionToHttpCodeConverter $httpExceptionToHttpCodeConverter;

    protected function setUp(): void
    {
        $this->httpExceptionToHttpCodeConverter = new HttpExceptionToHttpCodeConverter();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ErrorToHttpCodeConverterInterface::class,
            $this->httpExceptionToHttpCodeConverter
        );
    }

    #[DataProvider('provideConvertToHttpCodeCases')]
    public function testConvertToHttpCode(\Throwable $throwable, ?int $expectedErrorCode): void
    {
        static::assertSame(
            $expectedErrorCode,
            $this->httpExceptionToHttpCodeConverter->convertToHttpCode($throwable)
        );
    }

    public static function provideConvertToHttpCodeCases(): iterable
    {
        yield 'Default' => [
            new \Exception(),
            null,
        ];

        yield 'Base Class' => [
            new HttpException(400),
            400,
        ];

        yield 'Sub Class' => [
            new UnprocessableEntityHttpException(),
            422,
        ];
    }
}
