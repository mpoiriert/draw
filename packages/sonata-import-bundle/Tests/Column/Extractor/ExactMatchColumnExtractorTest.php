<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\Extractor\ExactMatchColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExactMatchColumnExtractor::class)]
class ExactMatchColumnExtractorTest extends TestCase
{
    public function testGetDefaultPriority(): void
    {
        $object = new ExactMatchColumnExtractor(
            static::createStub(Importer::class)
        );

        static::assertSame(
            -1000,
            $object::getDefaultPriority()
        );
    }

    public function testGetOptions(): void
    {
        $object = new ExactMatchColumnExtractor(
            static::createStub(Importer::class)
        );

        static::assertSame(
            ['test'],
            $object->getOptions(
                new Column(),
                ['test']
            )
        );
    }

    public function testExtractDefaultValueAlreadySet(): void
    {
        $object = new ExactMatchColumnExtractor(
            $importer = $this->createMock(Importer::class)
        );

        $importer
            ->expects(static::never())
            ->method('getOptions')
            ->seal()
        ;

        static::assertNull(
            $object->extractDefaultValue(
                new Column()
                    ->setHeaderName('headerName')
                    ->setMappedTo('mappedTo'),
                ['sample1', 'sample2']
            )
        );
    }

    public function testExtractDefaultValueNotInOptions(): void
    {
        $object = new ExactMatchColumnExtractor(
            $importer = $this->createMock(Importer::class)
        );

        $importer
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn(['headerName1', 'headerName2'])
            ->seal()
        ;

        static::assertNull(
            $object->extractDefaultValue(
                new Column()
                    ->setHeaderName('headerName'),
                ['sample3', 'sample4']
            )
        );
    }

    public function testExtractDefaultValueInOptions(): void
    {
        $object = new ExactMatchColumnExtractor(
            $importer = $this->createMock(Importer::class)
        );

        $importer
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn(['headerName'])
            ->seal()
        ;

        $column = new Column()
            ->setHeaderName('headerName')
        ;

        $column = $object->extractDefaultValue(
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
