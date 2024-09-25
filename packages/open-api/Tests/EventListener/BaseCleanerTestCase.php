<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Root;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class BaseCleanerTestCase extends TestCase
{
    abstract public static function getFixtureDir(): string;

    abstract public function clean(CleanEvent $cleanEvent): void;

    public static function provideTestClean(): iterable
    {
        foreach (glob(__DIR__.'/fixture/cleaner/'.static::getFixtureDir().'/*-dirty.json') as $file) {
            yield str_replace('-dirty.json', '', basename($file)) => [$file, str_replace('dirty.json', 'clean.json', $file)];
        }
    }

    #[DataProvider('provideTestClean')]
    public function testClean(string $dirty, string $clean): void
    {
        $openApi = new OpenApi();
        $schema = $openApi->extract(file_get_contents($dirty));
        static::assertInstanceOf(Root::class, $schema);

        $this->clean($event = new CleanEvent($schema, new ExtractionContext($openApi)));

        $cleanedSchema = $event->getRootSchema();

        static::assertEquals(
            json_decode(file_get_contents($clean), true, 512, \JSON_THROW_ON_ERROR),
            json_decode($openApi->dump($cleanedSchema, false), true, 512, \JSON_THROW_ON_ERROR)
        );
    }
}
