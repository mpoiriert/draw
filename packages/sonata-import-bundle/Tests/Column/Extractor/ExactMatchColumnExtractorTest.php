<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Column\Extractor\ExactMatchColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExactMatchColumnExtractor::class)]
class ExactMatchColumnExtractorTest extends TestCase
{
    private ExactMatchColumnExtractor $object;

    private Importer&MockObject $importer;

    protected function setUp(): void
    {
        $this->object = new ExactMatchColumnExtractor(
            $this->importer = $this->createMock(Importer::class)
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

    public function testExtractDefaultValueAlreadySet(): void
    {
        $this->importer
            ->expects(static::never())
            ->method('getOptions')
        ;

        static::assertNull(
            $this->object->extractDefaultValue(
                (new Column())
                    ->setHeaderName('headerName')
                    ->setMappedTo('mappedTo'),
                ['sample1', 'sample2']
            )
        );
    }

    public function testExtractDefaultValueNotInOptions(): void
    {
        $this->importer
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn(['headerName1', 'headerName2'])
        ;

        static::assertNull(
            $this->object->extractDefaultValue(
                (new Column())
                    ->setHeaderName('headerName'),
                ['sample3', 'sample4']
            )
        );
    }

    public function testExtractDefaultValueInOptions(): void
    {
        $this->importer
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn(['headerName'])
        ;

        $column = (new Column())
            ->setHeaderName('headerName')
        ;

        $column = $this->object->extractDefaultValue(
            $column,
            ['sample5', 'sample6']
        );

        static::assertSame(
            'headerName',
            $column->getMappedTo()
        );

        static::assertNull(
            $column->getHeaderName()
        );

        static::assertNull(
            $column->getIsIdentifier()
        );

        static::assertNull(
            $column->getIsIgnored()
        );

        static::assertNull(
            $column->getIsDate()
        );
    }
}
