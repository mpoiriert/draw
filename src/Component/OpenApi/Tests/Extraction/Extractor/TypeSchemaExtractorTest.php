<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor;

use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Schema;
use PHPUnit\Framework\TestCase;

class TypeSchemaExtractorTest extends TestCase
{
    public function provideTestCanExtract()
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

        $this->assertSame($canBeExtract, $extractor->canExtract($source, $type, $context));

        if (!$canBeExtract) {
            try {
                $extractor->extract($source, $type, $context);
                $this->fail('should throw a exception of type [Draw\Component\OpenApi\Extraction\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testExtract()
    {
        $extractor = new TypeSchemaExtractor();

        $context = $this->getExtractionContext();
        $context->getOpenApi()->registerExtractor($extractor);

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

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/typeSchemaExtractorTestExtract.json'),
            $jsonSchema
        );
    }

    public function getExtractionContext()
    {
        $openApi = new OpenApi();
        $schema = $openApi->extract('{"swagger":"2.0","definitions":{}}');

        return new ExtractionContext($openApi, $schema);
    }
}

class TypeExtractorStubModel
{
}
