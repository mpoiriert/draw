<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\Schema\Schema;
use PHPUnit\Framework\TestCase;

class TypeSchemaExtractorTest extends TestCase
{
    public function provideTestCanExtract(): iterable
    {
        return [
            ['string', null, false],
            [null, new Schema(), false],
            ['string', new Schema(), true],
            ['string[]', new Schema(), true],
            [new Schema(), new Schema(), false],
        ];
    }

    /**
     * @dataProvider provideTestCanExtract
     *
     * @param $source
     * @param $type
     * @param $canBeExtract
     */
    public function testCanExtract($source, $type, $canBeExtract)
    {
        $extractor = new TypeSchemaExtractor();

        /** @var ExtractionContextInterface $context */
        $context = $this->getMockForAbstractClass(ExtractionContextInterface::class);

        static::assertSame($canBeExtract, $extractor->canExtract($source, $type, $context));

        if (!$canBeExtract) {
            try {
                $extractor->extract($source, $type, $context);
                static::fail('should throw a exception of type [Draw\Component\OpenApi\Exception\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException $e) {
                static::assertTrue(true);
            }
        }
    }

    public function testExtract()
    {
        $extractor = new TypeSchemaExtractor();

        $context = $this->getExtractionContext([$extractor]);

        $schema = $context->getRootSchema();

        $schema->addDefinition('fake-string', $modelSchema = new Schema());
        $extractor->extract('string', $modelSchema, $context);

        $schema->addDefinition('fake-strings', $modelSchema = new Schema());
        $extractor->extract('string[]', $modelSchema, $context);

        $schema->addDefinition('fake-strings', $modelSchema = new Schema());
        $extractor->extract('string[]', $modelSchema, $context);

        $schema->addDefinition('object', $modelSchema = new Schema());
        $extractor->extract(TypeExtractorStubModel::class, $modelSchema, $context);

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
