<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Import;

use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Column\ColumnFactory;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use Draw\Bundle\SonataImportBundle\Tests\Import\Fixtures\CallTrackingColumnExtractor;
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

        $this->assertSame('_SKIP_', $importer->getSkipValue());
        $this->assertTrue($importer->isSkipValue('_SKIP_'));
        $this->assertFalse($importer->isSkipValue(''));
        $this->assertFalse($importer->isSkipValue(null));
        $this->assertFalse($importer->isSkipValue(0));
        $this->assertFalse($importer->isSkipValue('skip'));
        $this->assertFalse($importer->isSkipValue(' _SKIP_ '));
        $this->assertFalse($importer->isSkipValue(new \DateTime()));
    }

    public function testIsSkipValueCustomMarker(): void
    {
        $importer = $this->createImporter(skipValue: '*SKIP*');

        $this->assertSame('*SKIP*', $importer->getSkipValue());
        $this->assertTrue($importer->isSkipValue('*SKIP*'));
        $this->assertFalse($importer->isSkipValue('_SKIP_'));
    }

    public function testAssignValueSkipsExtractorsForMarker(): void
    {
        $extractor = new CallTrackingColumnExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())->setMappedTo('label');

        $this->invokeAssign($importer, $object, $column, '_SKIP_');

        $this->assertSame(0, $extractor->callCount, 'Extractors must not be invoked for skip values.');
        $this->assertObjectNotHasProperty('label', $object);
    }

    public function testAssignValueSkipsBeforeDateCoercion(): void
    {
        $extractor = new CallTrackingColumnExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())
            ->setMappedTo('createdAt')
            ->setIsDate(true)
        ;

        $this->invokeAssign($importer, $object, $column, '_SKIP_');

        $this->assertSame(0, $extractor->callCount);
    }

    public function testAssignValueRunsExtractorsForRegularValue(): void
    {
        $extractor = new CallTrackingColumnExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())->setMappedTo('label');

        $this->invokeAssign($importer, $object, $column, 'Real value');

        $this->assertSame(1, $extractor->callCount);
        $this->assertSame('Real value', $extractor->lastValue);
    }

    public function testAssignValueEmptyStringIsNotASkip(): void
    {
        $extractor = new CallTrackingColumnExtractor();
        $importer = $this->createImporter([$extractor]);

        $object = new \stdClass();
        $column = (new Column())->setMappedTo('label');

        $this->invokeAssign($importer, $object, $column, '');

        $this->assertSame(1, $extractor->callCount, 'Empty string must still be passed to extractors so the field can be cleared.');
        $this->assertSame('', $extractor->lastValue);
    }

    #[DataProvider('provideIsSkipValueRejectsNonExactMatchesCases')]
    public function testIsSkipValueRejectsNonExactMatches(mixed $value): void
    {
        $importer = $this->createImporter();

        $this->assertFalse($importer->isSkipValue($value));
    }

    public static function provideIsSkipValueRejectsNonExactMatchesCases(): iterable
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

    private function invokeAssign(Importer $importer, object $object, Column $column, mixed $value): void
    {
        $reflection = new \ReflectionMethod(Importer::class, 'assignValue');
        $reflection->invoke($importer, $object, $column, $value);
    }
}
