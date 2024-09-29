<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\JsonRootSchemaExtractor;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\Tester\MockTrait;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RootSchemaExtractorTest extends TestCase
{
    use MockTrait;

    public static function provideTestCanExtract(): array
    {
        return [
            [[], new Root(), false],
            ['toto', new Root(), false],
            ['{}', new Root(), false],
            ['{"swagger":"1.0"}', new Root(), false],
            ['{"swagger":"2.0"}', '', false],
            ['{"swagger":"2.0"}', new \stdClass(), false],
        ];
    }

    #[DataProvider('provideTestCanExtract')]
    public function testCanExtract(mixed $source, mixed $type, bool $expected): void
    {
        $extractor = new JsonRootSchemaExtractor(SerializerBuilder::create()->build());

        static::assertSame(
            $expected,
            $extractor->canExtract(
                $source,
                $type,
                $context = $this->createMock(ExtractionContextInterface::class)
            )
        );

        if ($expected) {
            $extractor->extract($source, $type, $context);
            static::assertTrue(true);
        } else {
            try {
                $extractor->extract($source, $type, $context);
                static::fail('should throw a exception of type [Draw\Component\OpenApi\Exception\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException) {
                static::assertTrue(true);
            }
        }
    }
}
