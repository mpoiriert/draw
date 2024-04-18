<?php

namespace Draw\Bundle\SonataImportBundle\Tests;

use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\NamedBaseIdentifierColumnBuilder;
use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ReflectionColumnBuilder;
use Draw\Bundle\SonataImportBundle\Column\ColumnFactory;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[
    CoversClass(ColumnFactory::class),
    CoversClass(NamedBaseIdentifierColumnBuilder::class),
    CoversClass(ReflectionColumnBuilder::class),
]
class ColumnFactoryTest extends TestCase
{
    private ColumnFactory $columnFactory;

    protected function setUp(): void
    {
        $this->columnFactory = new ColumnFactory(
            [
                new NamedBaseIdentifierColumnBuilder(),
                new ReflectionColumnBuilder(),
            ]
        );
    }

    public function testGenerateColumnsIdentifier(): void
    {
        $columns = $this->columnFactory
            ->generateColumns(
                Import::class,
                ['id'],
                [[12]]
            );

        static::assertCount(1, $columns);

        $column = $columns[0];

        static::assertInstanceOf(Column::class, $column);

        static::assertSame('id', $column->getHeaderName());
        static::assertSame('id', $column->getMappedTo());
        static::assertTrue($column->getIsIdentifier());
        static::assertFalse($column->getIsIgnored());
        static::assertFalse($column->getIsDate());
    }

    public function testGenerateColumnsDate(): void
    {
        $columns = $this->columnFactory
            ->generateColumns(
                Import::class,
                ['createdAt'],
                ['2018-10-10']
            );

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
