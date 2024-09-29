<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\Doctrine\Extractor;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\SonataImportBundle\Column\Bridge\Doctrine\Extractor\DoctrineAssociationColumnExtractor;
use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Component\Tester\DoctrineOrmTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DoctrineAssociationColumnExtractorTest extends TestCase
{
    use DoctrineOrmTrait;

    private DoctrineAssociationColumnExtractor $object;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->object = new DoctrineAssociationColumnExtractor(
            static::createRegistry(
                $this->entityManager = static::setUpMySqlWithAttributeDriver([
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
                'import.id',
            ],
            $this->object->getOptions(
                $this->createColumn(),
                ['kept']
            )
        );
    }

    public function testExtractDefaultValue(): void
    {
        $columnInfo = $this->object->extractDefaultValue(
            $this->createColumn()
                ->setHeaderName('import.id'),
            []
        );

        static::assertNull($columnInfo);
    }

    public function testAssign(): void
    {
        $import = (new Import())
            ->setEntityClass(\stdClass::class)
        ;

        $this->entityManager->persist($import);
        $this->entityManager->flush();

        $column = $this->createColumn()
            ->setMappedTo('import.id')
        ;

        $object = new Column();

        static::assertTrue(
            $this->object->assign(
                $object,
                $column,
                $import->getId(),
            )
        );

        static::assertSame(
            $import,
            $object->getImport()
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
