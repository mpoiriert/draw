<?php

namespace Draw\Component\OpenApi\Tests;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\SchemaCleaner;
use PHPUnit\Framework\TestCase;

class SchemaCleanerTest extends TestCase
{
    /**
     * @var SchemaCleaner
     */
    private $schemaCleaner;

    public function setUp(): void
    {
        $this->schemaCleaner = new SchemaCleaner();
    }

    public function provideTestClean(): iterable
    {
        return [
            'simple' => ['simple'],
            'difference' => ['difference'],
            'deep-reference' => ['deep-reference'],
            'not-needed-model' => ['not-needed-model'],
            'definition-index' => ['definition-index'],
            'keep' => ['keep'],
        ];
    }

    /**
     * @dataProvider provideTestClean
     *
     * @param $case
     */
    public function testClean($case)
    {
        $openApi = new OpenApi();
        $schema = $openApi->extract(
            file_get_contents(__DIR__.'/fixture/cleaner/'.$case.'-dirty.json')
        );
        $this->assertInstanceOf(\Draw\Component\OpenApi\Schema\Root::class, $schema);

        $cleanedSchema = $this->schemaCleaner->clean($schema);

        $this->assertEquals(
            json_decode(file_get_contents(__DIR__.'/fixture/cleaner/'.$case.'-clean.json'), true),
            json_decode($openApi->dump($cleanedSchema, false), true)
        );
    }
}
