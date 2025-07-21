<?php

namespace Draw\Component\OpenApi\Tests\HttpFoundation\ErrorToHttpCodeConverter;

use Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter\ConfigurableErrorToHttpCodeConverter;
use Draw\Component\OpenApi\HttpFoundation\ErrorToHttpCodeConverter\ErrorToHttpCodeConverterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConfigurableErrorToHttpCodeConverterTest extends TestCase
{
    private ConfigurableErrorToHttpCodeConverter $errorToHttpCodeConverter;

    protected function setUp(): void
    {
        $this->errorToHttpCodeConverter = new ConfigurableErrorToHttpCodeConverter();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ErrorToHttpCodeConverterInterface::class,
            $this->errorToHttpCodeConverter
        );
    }

    /**
     * @param array<string,int> $errorCodes
     */
    #[DataProvider('provideConvertToHttpCodeCases')]
    public function testConvertToHttpCode(\Throwable $throwable, array $errorCodes, int $errorCode): void
    {
        $this->errorToHttpCodeConverter = new ConfigurableErrorToHttpCodeConverter($errorCodes);

        static::assertSame(
            $errorCode,
            $this->errorToHttpCodeConverter->convertToHttpCode($throwable)
        );
    }

    public static function provideConvertToHttpCodeCases(): iterable
    {
        yield 'Default' => [
            new \Exception(),
            [],
            500,
        ];

        yield 'ChangeDefault' => [
            new \Exception(),
            [\Exception::class => 400],
            400,
        ];

        yield 'FallbackOnDefault' => [
            new \Exception(),
            [\RuntimeException::class => 400],
            500,
        ];

        yield 'MultipleConfiguration' => [
            new \Exception(),
            [\RuntimeException::class => 400, \Exception::class => 300],
            300,
        ];

        yield 'Extend' => [
            new \OutOfBoundsException(),
            [\RuntimeException::class => 400, \Exception::class => 300],
            400,
        ];

        $exception = new class extends \Exception implements \JsonSerializable {
            public function jsonSerialize(): void
            {
            }
        };

        yield 'Implements' => [
            $exception,
            [\RuntimeException::class => 400, \JsonSerializable::class => 300],
            300,
        ];
    }
}
