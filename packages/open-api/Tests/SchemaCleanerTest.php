<?php

namespace Draw\Component\OpenApi\Tests;

use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use Draw\Component\OpenApi\SchemaCleaner;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\OpenApi\SchemaCleaner
 */
class SchemaCleanerTest extends TestCase
{
    private SchemaCleaner $object;

    protected function setUp(): void
    {
        $this->object = new SchemaCleaner();
    }

    public function provideTestClean(): iterable
    {
        foreach (glob(__DIR__.'/fixture/cleaner/*-dirty.json') as $file) {
            yield str_replace('-dirty.json', '', basename($file)) => [$file, str_replace('dirty.json', 'clean.json', $file)];
        }
    }

    /**
     * @dataProvider provideTestClean
     */
    public function testClean(string $dirty, string $clean): void
    {
        $openApi = new OpenApi();
        $schema = $openApi->extract(file_get_contents($dirty));
        static::assertInstanceOf(Root::class, $schema);

        $cleanedSchema = $this->object->clean($schema);

        static::assertEquals(
            json_decode(file_get_contents($clean), true, 512, \JSON_THROW_ON_ERROR),
            json_decode($openApi->dump($cleanedSchema, false), true, 512, \JSON_THROW_ON_ERROR)
        );
    }
}
