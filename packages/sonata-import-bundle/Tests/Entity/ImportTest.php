<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Validation;

/**
 * @internal
 */
class ImportTest extends TestCase
{
    private Import $entity;

    protected function setUp(): void
    {
        $this->entity = new Import();
    }

    public function testIdMutator(): void
    {
        $this->assertNull($this->entity->getId());
        $this->assertSame(
            $this->entity,
            $this->entity->setId(999)
        );
        $this->assertSame(999, $this->entity->getId());
    }

    public function testEntityClassMutator(): void
    {
        $this->assertNull($this->entity->getEntityClass());

        $this->assertSame(
            $this->entity,
            $this->entity->setEntityClass($entityClass = 'test')
        );

        $this->assertSame($entityClass, $this->entity->getEntityClass());
    }

    public function testFileContentMutator(): void
    {
        $this->assertNull($this->entity->getFileContent());

        $this->assertSame(
            $this->entity,
            $this->entity->setFileContent($fileContent = 'file content')
        );

        $this->assertSame($fileContent, $this->entity->getFileContent());
    }

    public function testStateMutator(): void
    {
        $this->assertSame(Import::STATE_NEW, $this->entity->getState());

        $this->assertSame(
            $this->entity,
            $this->entity->setState($state = Import::STATE_CONFIGURATION)
        );

        $this->assertSame($state, $this->entity->getState());
    }

    public function testColumnsMutator(): void
    {
        $collection = $this->entity->getColumns();
        static::assertCount(0, $collection);

        $this->assertSame(
            $this->entity,
            $this->entity->addColumn($column = new Column())
        );

        $this->assertSame($this->entity, $column->getImport());

        $this->assertTrue($collection->contains($column));

        $this->assertSame(
            $this->entity,
            $this->entity->removeColumn($column)
        );

        $this->assertFalse($collection->contains($column));
    }

    public function testGetUpdatedAtMutator(): void
    {
        $this->assertNull($this->entity->getUpdatedAt());

        $this->assertSame(
            $this->entity,
            $this->entity->setUpdatedAt($dateTime = new \DateTime())
        );

        $this->assertSame($dateTime, $this->entity->getUpdatedAt());
    }

    public function testGetCreatedAtMutator(): void
    {
        $this->assertNull($this->entity->getCreatedAt());

        $this->assertSame(
            $this->entity,
            $this->entity->setCreatedAt($dateTime = new \DateTime())
        );

        $this->assertSame($dateTime, $this->entity->getCreatedAt());
    }

    public function testUpdateTimeStamp(): void
    {
        $this->assertNull($this->entity->getCreatedAt());
        $this->assertNull($this->entity->getUpdatedAt());

        $this->entity->updateTimestamp(
            new LifecycleEventArgs(
                $this->entity,
                static::createStub(EntityManagerInterface::class)
            )
        );

        $this->assertInstanceOf(\DateTime::class, $this->entity->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $dateTime = $this->entity->getUpdatedAt());

        $this->entity->updateTimestamp(
            new LifecycleEventArgs(
                $this->entity,
                static::createStub(ObjectManager::class)
            )
        );

        $this->assertNotSame($dateTime, $dateTime = $this->entity->getUpdatedAt());

        /** @var array<string, array> $changeSet */
        $changeSet = ['updatedAt' => []];
        $this->entity->updateTimestamp(
            new PreUpdateEventArgs(
                $this->entity,
                static::createStub(EntityManagerInterface::class),
                $changeSet
            )
        );

        $this->assertSame($dateTime, $this->entity->getUpdatedAt());
    }

    public function testGetGroupSequence(): void
    {
        $this->assertSame(
            ['Import', $this->entity->getState()],
            $this->entity->getGroupSequence()
        );
    }

    public function testGetColumnMapping(): void
    {
        $this->entity->addColumn($column1 = new Column());
        $column1->setHeaderName('Column1');

        $this->entity->addColumn($column = new Column());
        $column->setIsIdentifier(true); // Identifier columns are ignored

        $this->entity->addColumn($column = new Column());
        $column->setIsIgnored(true);

        $this->assertSame(
            ['Column1' => $column1],
            $this->entity->getColumnMapping()
        );
    }

    public function testValidateForProcessing(): void
    {
        $constraint = new Callback('validateForProcessing');
        $validator = Validation::createValidator();

        $this->assertCount(1, $violations = $validator->validate($this->entity, $constraint));
        $this->assertSame(
            'You need a identifier column.',
            $violations[0]->getMessage()
        );
        $this->assertSame(
            'columns',
            $violations[0]->getPropertyPath()
        );

        $this->entity->addColumn($column = new Column());
        $column->setHeaderName('Id');
        $column->setIsIdentifier(true);
        $column->setIsIgnored(true);

        $this->assertCount(1, $violations = $validator->validate($this->entity, $constraint));
        $this->assertSame(
            'Identifier column "Id" cannot be ignored.',
            $violations[0]->getMessage()
        );
        $this->assertSame(
            'columns[0]',
            $violations[0]->getPropertyPath()
        );
    }

    public function testToString(): void
    {
        $this->assertSame('', $this->entity->__toString());

        $this->entity->setId(999);

        $this->assertSame('999', $this->entity->__toString());
    }
}
