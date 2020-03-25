<?php namespace Draw\Component\OpenApi\Tests;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use PHPUnit\Framework\TestCase;

class OpenApiTest extends TestCase
{
    public function provideTestExtractSwaggerSchema()
    {
        $result = array();
        foreach(glob(__DIR__ . '/fixture/schema/*.json') as $file) {
            $result[] = array($file);
        }

        return $result;
    }

    /**
     * @dataProvider provideTestExtractSwaggerSchema
     * @param $file
     */
    public function testExtractSwaggerSchema($file)
    {
        $openApi = new OpenApi();

        $schema = $openApi->extract(file_get_contents($file));
        $this->assertInstanceOf(Root::class, $schema);

        $this->assertJsonStringEqualsJsonString(file_get_contents($file), $openApi->dump($schema, false));
    }
}