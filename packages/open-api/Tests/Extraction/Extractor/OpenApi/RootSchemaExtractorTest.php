<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\OpenApi;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\JsonRootSchemaExtractor;
use Draw\Component\OpenApi\Schema\Root;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;

class RootSchemaExtractorTest extends TestCase
{
    public function provideTestCanExtract()
    {
        return [
            [[], new Root(), false],
            ['toto', new Root(), false],
            ['{}', new Root(), false],
            ['{"swagger":"1.0"}', new Root(), false],
            ['{"swagger":"2.0"}', '', false],
            ['{"swagger":"2.0"}', new stdClass(), false],
        ];
    }

    /**
     * @dataProvider provideTestCanExtract
     *
     * @param $source
     * @param $type
     * @param $expected
     */
    public function testCanExtract($source, $type, $expected)
    {
        $extractor = new JsonRootSchemaExtractor(SerializerBuilder::create()->build());

        /** @var ExtractionContextInterface $context */
        $context = $this->getMockForAbstractClass(ExtractionContextInterface::class);

        $this->assertSame($expected, $extractor->canExtract($source, $type, $context));

        if ($expected) {
            $extractor->extract($source, $type, $context);
            $this->assertTrue(true);
        } else {
            try {
                $extractor->extract($source, $type, $context);
                $this->fail('should throw a exception of type [Draw\Component\OpenApi\Exception\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException $e) {
                $this->assertTrue(true);
            }
        }
    }
}
