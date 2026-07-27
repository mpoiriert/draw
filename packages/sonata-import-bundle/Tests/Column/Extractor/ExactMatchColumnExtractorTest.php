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
        $this->assertInstanceOf(
            ColumnExtractorInterface::class,
            $this->object
        );
    }

    public function testGetDefaultPriority(): void
    {
        $this->assertSame(
            -1000,
            $this->object::getDefaultPriority()
        );
    }

    public function testGetOptions(): void
    {
        $this->assertSame(
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
            ->expects($this->never())
            ->method('getOptions')
        ;

        $this->assertNull(
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
            ->expects($this->once())
            ->method('getOptions')
            ->willReturn(['headerName1', 'headerName2'])
        ;

        $this->assertNull(
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
            ->expects($this->once())
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

        $this->assertSame(
            'headerName',
            $column->getMappedTo()
        );

        $this->assertNull(
            $column->getHeaderName()
        );

        $this->assertNull(
            $column->getIsIdentifier()
        );

        $this->assertNull(
            $column->getIsIgnored()
        );

        $this->assertNull(
            $column->getIsDate()
        );
    }
}
