<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class TypeSchemaExtractorTest extends TestCase
{
    private TypeSchemaExtractor $object;

    protected function setUp(): void
    {
        $this->object = new TypeSchemaExtractor();
    }

    #[DataProvider('provideCanExtractCases')]
    public function testCanExtract(mixed $source, mixed $type, bool $canBeExtract): void
    {
        $context = $this->createMock(ExtractionContextInterface::class);

        static::assertSame(
            $canBeExtract,
            $this->object->canExtract($source, $type, $context)
        );

        if (!$canBeExtract) {
            $this->expectExceptionObject(new ExtractionImpossibleException());
            $this->object->extract($source, $type, $context);
        }
    }

    public static function provideCanExtractCases(): iterable
    {
        yield 'invalid-target' => [
            'string',
            null,
            false,
        ];

        yield 'null-source' => [
            null,
            new Schema(),
            false,
        ];

        yield 'primitive-source' => [
            'string',
            new Schema(),
            true,
        ];

        yield 'primitive[]-source' => [
            'string[]',
            new Schema(),
            true,
        ];

        yield 'array<primitive>-source' => [
            'array<string>',
            new Schema(),
            true,
        ];
    }

    public function testExtract(): void
    {
        $context = $this->getExtractionContext([$this->object]);

        $schema = $context->getRootSchema();

        $schema->addDefinition('fake-string', $modelSchema = new Schema());
        $this->object->extract('string', $modelSchema, $context);

        $schema->addDefinition('fake-strings', $modelSchema = new Schema());
        $this->object->extract('string[]', $modelSchema, $context);

        $schema->addDefinition('fake-integers', $modelSchema = new Schema());
        $this->object->extract('array<int>', $modelSchema, $context);

        $schema->addDefinition('object', $modelSchema = new Schema());
        $this->object->extract(TypeExtractorStubModel::class, $modelSchema, $context);

        $jsonSchema = $context->getOpenApi()->dump($context->getRootSchema(), false);

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/typeSchemaExtractorTestExtract.json'),
            $jsonSchema
        );
    }

    public function getExtractionContext(array $extractors = []): ExtractionContextInterface
    {
        return new ExtractionContext(new OpenApi($extractors), new Root());
    }
}

class TypeExtractorStubModel
{
}
