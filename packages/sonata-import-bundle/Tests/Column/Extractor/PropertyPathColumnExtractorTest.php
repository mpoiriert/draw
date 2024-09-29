<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Column\Extractor\PropertyPathColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PropertyPathColumnExtractorTest extends TestCase
{
    private PropertyPathColumnExtractor $object;

    protected function setUp(): void
    {
        $this->object = new PropertyPathColumnExtractor();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ColumnExtractorInterface::class,
            $this->object
        );
    }

    public function testGetDefaultPriority(): void
    {
        static::assertSame(
            -1000,
            $this->object::getDefaultPriority()
        );
    }

    public function testGetOptions(): void
    {
        static::assertSame(
            ['test'],
            $this->object->getOptions(
                new Column(),
                ['test']
            )
        );
    }

    public function testExtractDefaultValue(): void
    {
        static::assertNull(
            $this->object->extractDefaultValue(
                (new Column())
                    ->setMappedTo('test'),
                []
            )
        );
    }

    public function testAssign(): void
    {
        $object = new class {
            public string $test;
        };

        $this->object->assign(
            $object,
            (new Column())
                ->setMappedTo('test'),
            $value = 'value'
        );

        static::assertSame(
            $value,
            $object->test
        );
    }
}
