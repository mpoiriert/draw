<?php

namespace Draw\Bundle\SonataImportBundle\Tests;

use Draw\Bundle\SonataImportBundle\Column\ColumnFactory;
use Draw\Bundle\SonataImportBundle\Column\Extractor\PropertyPathColumnExtractor;
use Draw\Bundle\SonataImportBundle\Column\Extractor\SetterMethodReflectionColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[
    CoversClass(ColumnFactory::class),
    CoversClass(SetterMethodReflectionColumnExtractor::class),
    CoversClass(PropertyPathColumnExtractor::class),
]
class ColumnFactoryTest extends TestCase
{
    private ColumnFactory $columnFactory;

    protected function setUp(): void
    {
        $this->columnFactory = new ColumnFactory(
            [
                new SetterMethodReflectionColumnExtractor(),
                new PropertyPathColumnExtractor(),
            ]
        );
    }

    public function testGenerateColumnsDate(): void
    {
        $import = (new Import())
            ->setEntityClass(Import::class)
        ;

        $this->columnFactory
            ->buildColumns(
                $import,
                ['createdAt'],
                ['2018-10-10']
            )
        ;

        $columns = $import->getColumns()->toArray();

        static::assertCount(1, $columns);

        $column = $columns[0];

        static::assertInstanceOf(Column::class, $column);

        static::assertSame('createdAt', $column->getHeaderName());
        static::assertSame('createdAt', $column->getMappedTo());
        static::assertFalse($column->getIsIdentifier());
        static::assertFalse($column->getIsIgnored());
        static::assertTrue($column->getIsDate());
    }
}
