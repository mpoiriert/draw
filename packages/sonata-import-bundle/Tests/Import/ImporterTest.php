<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Import;

use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Column\ColumnFactory;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @internal
 */
#[CoversClass(Importer::class)]
class ImporterTest extends TestCase
{
    public function testIsSkipValueDefault(): void
    {
        $importer = $this->createImporter();

        static::assertSame('_SKIP_', $importer->getSkipValue());
        static::assertTrue($importer->isSkipValue('_SKIP_'));
        static::assertFalse($importer->isSkipValue(''));
        static::assertFalse($importer->isSkipValue(null));
        static::assertFalse($importer->isSkipValue(0));
        static::assertFalse($importer->isSkipValue('skip'));
        static::assertFalse($importer->isSkipValue(' _SKIP_ '));
        static::assertFalse($importer->isSkipValue(new \DateTime()));
    }

    public function testIsSkipValueCustomMarker(): void
    {
        $importer = $this->createImporter(skipValue: '*SKIP*');

        static::assertSame('*SKIP*', $importer->getSkipValue());
        static::assertTrue($importer->isSkipValue('*SKIP*'));
        static::assertFalse($importer->isSkipValue('_SKIP_'));
    }

    public function testAssignValueSkipsExtractorsForMarker(): void
    {
        $extractor = $this->createCallTrackingExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())->setMappedTo('label');

        $this->invokeAssign($importer, $object, $column, '_SKIP_');

        static::assertSame(0, $extractor->callCount, 'Extractors must not be invoked for skip values.');
        static::assertObjectNotHasProperty('label', $object);
    }

    public function testAssignValueSkipsBeforeDateCoercion(): void
    {
        $extractor = $this->createCallTrackingExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())
            ->setMappedTo('createdAt')
            ->setIsDate(true)
        ;

        $this->invokeAssign($importer, $object, $column, '_SKIP_');

        static::assertSame(0, $extractor->callCount);
    }

    public function testAssignValueRunsExtractorsForRegularValue(): void
    {
        $extractor = $this->createCallTrackingExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())->setMappedTo('label');

        $this->invokeAssign($importer, $object, $column, 'Real value');

        static::assertSame(1, $extractor->callCount);
        static::assertSame('Real value', $extractor->lastValue);
    }

    public function testAssignValueEmptyStringIsNotASkip(): void
    {
        $extractor = $this->createCallTrackingExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())->setMappedTo('label');

        $this->invokeAssign($importer, $object, $column, '');

        static::assertSame(1, $extractor->callCount, 'Empty string must still be passed to extractors so the field can be cleared.');
        static::assertSame('', $extractor->lastValue);
    }

    public static function provideNonSkipValues(): array
    {
        return [
            'empty string' => [''],
            'whitespace' => [' '],
            'lowercase skip' => ['_skip_'],
            'padded marker' => [' _SKIP_ '],
            'integer zero' => [0],
            'integer minus one' => [-1],
            'null' => [null],
            'similar marker' => ['SKIP'],
        ];
    }

    #[DataProvider('provideNonSkipValues')]
    public function testIsSkipValueRejectsNonExactMatches(mixed $value): void
    {
        $importer = $this->createImporter();

        static::assertFalse($importer->isSkipValue($value));
    }

    /**
     * @param iterable<ColumnExtractorInterface> $extractors
     */
    private function createImporter(iterable $extractors = [], string $skipValue = Importer::DEFAULT_SKIP_VALUE): Importer
    {
        return new Importer(
            $extractors,
            $this->createStub(\Doctrine\Persistence\ManagerRegistry::class),
            $this->createStub(ColumnFactory::class),
            $this->createStub(NotifierInterface::class),
            $skipValue,
        );
    }

    private function createCallTrackingExtractor(): BaseColumnExtractor
    {
        return new class extends BaseColumnExtractor {
            public int $callCount = 0;

            public mixed $lastValue = null;

            #[\Override]
            public function assign(object $object, Column $column, mixed $value): bool
            {
                ++$this->callCount;
                $this->lastValue = $value;

                return true;
            }
        };
    }

    private function invokeAssign(Importer $importer, object $object, Column $column, mixed $value): void
    {
        $reflection = new \ReflectionMethod(Importer::class, 'assignValue');
        $reflection->invoke($importer, $object, $column, $value);
    }
}
