<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\Doctrine\Extractor;

use Draw\Bundle\SonataImportBundle\Column\Bridge\Doctrine\Extractor\DoctrineFieldColumnExtractor;
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

    public function testGetDefaultPriority(): void
    {
        $this->assertSame(
            0,
            $this->object::getDefaultPriority()
        );
    }

    public function testGetOptions(): void
    {
        $this->assertSame(
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

        $this->assertNotNull($columnInfo);
        $this->assertSame(
            'headerName',
            $columnInfo->getMappedTo()
        );
        $this->assertFalse(
            $columnInfo->getIsDate()
        );
        $this->assertNull(
            $columnInfo->getIsIdentifier()
        );
        $this->assertNull(
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

        $this->assertNotNull($columnInfo);
        $this->assertSame(
            'createdAt',
            $columnInfo->getMappedTo()
        );
        $this->assertTrue(
            $columnInfo->getIsDate()
        );
        $this->assertNull(
            $columnInfo->getIsIdentifier()
        );
        $this->assertNull(
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

        $this->assertNotNull($columnInfo);
        $this->assertSame(
            'id',
            $columnInfo->getMappedTo()
        );
        $this->assertFalse(
            $columnInfo->getIsDate()
        );
        $this->assertTrue(
            $columnInfo->getIsIdentifier()
        );
        $this->assertNull(
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
