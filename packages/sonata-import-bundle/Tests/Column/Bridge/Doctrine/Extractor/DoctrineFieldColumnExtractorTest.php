<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\Doctrine\Extractor;

use Draw\Bundle\SonataImportBundle\Column\Bridge\Doctrine\Extractor\DoctrineFieldColumnExtractor;
use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Component\Tester\DoctrineOrmTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DoctrineFieldColumnExtractorTest extends TestCase
{
    use DoctrineOrmTrait;

    private DoctrineFieldColumnExtractor $object;

    protected function setUp(): void
    {
        $this->object = new DoctrineFieldColumnExtractor(
            static::createRegistry(
                static::setUpMySqlWithAttributeDriver([
                    \dirname((new \ReflectionClass(Column::class))->getFileName()),
                ])
            )
        );
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
            0,
            $this->object::getDefaultPriority()
        );
    }

    public function testGetOptions(): void
    {
        static::assertSame(
            [
                'kept',
                'id',
                'headerName',
                'sample',
                'isIdentifier',
                'isIgnored',
                'mappedTo',
                'isDate',
                'createdAt',
                'updatedAt',
            ],
            $this->object->getOptions(
                $this->createColumn(),
                ['kept']
            )
        );
    }

    public function testExtractDefaultValueSimple(): void
    {
        $columnInfo = $this->object->extractDefaultValue(
            $this->createColumn()
                ->setHeaderName('headerName'),
            []
        );

        static::assertNotNull($columnInfo);
        static::assertSame(
            'headerName',
            $columnInfo->getMappedTo()
        );
        static::assertFalse(
            $columnInfo->getIsDate()
        );
        static::assertNull(
            $columnInfo->getIsIdentifier()
        );
        static::assertNull(
            $columnInfo->getIsIgnored()
        );
    }

    public function testExtractDefaultValueDate(): void
    {
        $columnInfo = $this->object->extractDefaultValue(
            $this->createColumn()
                ->setHeaderName('createdAt'),
            []
        );

        static::assertNotNull($columnInfo);
        static::assertSame(
            'createdAt',
            $columnInfo->getMappedTo()
        );
        static::assertTrue(
            $columnInfo->getIsDate()
        );
        static::assertNull(
            $columnInfo->getIsIdentifier()
        );
        static::assertNull(
            $columnInfo->getIsIgnored()
        );
    }

    public function testExtractDefaultValueIdentifier(): void
    {
        $columnInfo = $this->object->extractDefaultValue(
            $this->createColumn()
                ->setHeaderName('id'),
            []
        );

        static::assertNotNull($columnInfo);
        static::assertSame(
            'id',
            $columnInfo->getMappedTo()
        );
        static::assertFalse(
            $columnInfo->getIsDate()
        );
        static::assertTrue(
            $columnInfo->getIsIdentifier()
        );
        static::assertNull(
            $columnInfo->getIsIgnored()
        );
    }

    private function createColumn(): Column
    {
        return (new Column())
            ->setImport(
                (new Import())
                    ->setEntityClass(Column::class)
            )
        ;
    }
}
