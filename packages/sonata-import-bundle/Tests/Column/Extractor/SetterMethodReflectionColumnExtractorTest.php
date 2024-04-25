<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Column\Extractor\SetterMethodReflectionColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SetterMethodReflectionColumnExtractor::class)]
class SetterMethodReflectionColumnExtractorTest extends TestCase
{
    private SetterMethodReflectionColumnExtractor $object;

    protected function setUp(): void
    {
        $this->object = new SetterMethodReflectionColumnExtractor();
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
                'test',
                'date',
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
                ->setHeaderName('test'),
            []
        );

        static::assertNotNull($columnInfo);
        static::assertSame(
            'test',
            $columnInfo->getMappedTo()
        );
        static::assertNull(
            $columnInfo->getIsIdentifier()
        );
        static::assertNull(
            $columnInfo->getIsDate()
        );
        static::assertNull(
            $columnInfo->getIsIgnored()
        );
    }

    public function testExtractDefaultValueDate(): void
    {
        $columnInfo = $this->object->extractDefaultValue(
            $this->createColumn()
                ->setHeaderName('date'),
            []
        );

        static::assertNotNull($columnInfo);
        static::assertSame(
            'date',
            $columnInfo->getMappedTo()
        );
        static::assertNull(
            $columnInfo->getIsIdentifier()
        );
        static::assertTrue(
            $columnInfo->getIsDate()
        );
        static::assertNull(
            $columnInfo->getIsIgnored()
        );
    }

    public function testExtractDefaultValueDateUnion(): void
    {
        $columnInfo = $this->object->extractDefaultValue(
            $this->createColumn()
                ->setHeaderName('dateUnion'),
            []
        );

        static::assertNull($columnInfo);
    }

    private function createColumn(): Column
    {
        return (new Column())
            ->setImport(
                (new Import())
                    ->setEntityClass(SetterClassStub::class)
            );
    }
}

class SetterClassStub
{
    private function setPrivate(string $test): void
    {
    }

    protected function setProtected(string $test): void
    {
        // This is only to prevent phpstan to complain about unused method
        $this->setPrivate($test);
    }

    public static function setStatic(string $test): void
    {
    }

    public function setTest(string $test): void
    {
    }

    public function setNoParameter(): void
    {
    }

    public function setMultipleParameters(string $test, string $test2): void
    {
    }

    public function setDate(\DateTimeInterface $dateTime): void
    {
    }

    public function setDateUnion(\DateTimeInterface|string $dateTime): void
    {
    }
}
