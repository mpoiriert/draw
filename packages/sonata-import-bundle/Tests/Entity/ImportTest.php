<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Draw\Bundle\SonataImportBundle\Entity\Import;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Validation;

class ImportTest extends TestCase
{
    use MockTrait;

    private Import $entity;

    protected function setUp(): void
    {
        $this->entity = new Import();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(GroupSequenceProviderInterface::class, $this->entity);
    }

    public function testIdMutator(): void
    {
        static::assertNull($this->entity->getId());
        static::assertSame(
            $this->entity,
            $this->entity->setId(999)
        );
        static::assertSame(999, $this->entity->getId());
    }

    public function testEntityClassMutator(): void
    {
        static::assertNull($this->entity->getEntityClass());

        static::assertSame(
            $this->entity,
            $this->entity->setEntityClass($entityClass = 'test')
        );

        static::assertSame($entityClass, $this->entity->getEntityClass());
    }

    public function testFileContentMutator(): void
    {
        static::assertNull($this->entity->getFileContent());

        static::assertSame(
            $this->entity,
            $this->entity->setFileContent($fileContent = 'file content')
        );

        static::assertSame($fileContent, $this->entity->getFileContent());
    }

    public function testStateMutator(): void
    {
        static::assertSame(Import::STATE_NEW, $this->entity->getState());

        static::assertSame(
            $this->entity,
            $this->entity->setState($state = Import::STATE_CONFIGURATION)
        );

        static::assertSame($state, $this->entity->getState());
    }

    public function testColumnsMutator(): void
    {
        static::assertInstanceOf(Collection::class, $collection = $this->entity->getColumns());
        static::assertCount(0, $collection);

        static::assertSame(
            $this->entity,
            $this->entity->addColumn($column = new Column())
        );

        static::assertSame($this->entity, $column->getImport());

        static::assertTrue($collection->contains($column));

        static::assertSame(
            $this->entity,
            $this->entity->removeColumn($column)
        );

        static::assertFalse($collection->contains($column));
    }

    public function testGetUpdatedAtMutator(): void
    {
        static::assertNull($this->entity->getUpdatedAt());

        static::assertSame(
            $this->entity,
            $this->entity->setUpdatedAt($dateTime = new \DateTime())
        );

        static::assertSame($dateTime, $this->entity->getUpdatedAt());
    }

    public function testGetCreatedAtMutator(): void
    {
        static::assertNull($this->entity->getCreatedAt());

        static::assertSame(
            $this->entity,
            $this->entity->setCreatedAt($dateTime = new \DateTime())
        );

        static::assertSame($dateTime, $this->entity->getCreatedAt());
    }

    public function testUpdateTimeStamp(): void
    {
        static::assertNull($this->entity->getCreatedAt());
        static::assertNull($this->entity->getUpdatedAt());

        $this->entity->updateTimestamp(
            new LifecycleEventArgs(
                $this->entity,
                $this->createMock(EntityManagerInterface::class)
            )
        );

        static::assertInstanceOf(\DateTime::class, $this->entity->getCreatedAt());
        static::assertInstanceOf(\DateTime::class, $dateTime = $this->entity->getUpdatedAt());

        $this->entity->updateTimestamp(
            new LifecycleEventArgs(
                $this->entity,
                $this->createMock(ObjectManager::class)
            )
        );

        static::assertNotSame($dateTime, $dateTime = $this->entity->getUpdatedAt());

        $changeSet = ['updatedAt' => []];
        $this->entity->updateTimestamp(
            new PreUpdateEventArgs(
                $this->entity,
                $this->createMock(EntityManagerInterface::class),
                $changeSet
            )
        );

        static::assertSame($dateTime, $this->entity->getUpdatedAt());
    }

    public function testGetGroupSequence(): void
    {
        static::assertSame(
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

        static::assertSame(
            ['Column1' => $column1],
            $this->entity->getColumnMapping()
        );
    }

    public function testValidateForProcessing(): void
    {
        $constraint = new Callback('validateForProcessing');
        $validator = Validation::createValidator();

        static::assertCount(1, $violations = $validator->validate($this->entity, $constraint));
        static::assertSame(
            'You need a identifier column.',
            $violations[0]->getMessage()
        );
        static::assertSame(
            'columns',
            $violations[0]->getPropertyPath()
        );

        $this->entity->addColumn($column = new Column());
        $column->setHeaderName('Id');
        $column->setIsIdentifier(true);
        $column->setIsIgnored(true);

        static::assertCount(1, $violations = $validator->validate($this->entity, $constraint));
        static::assertSame(
            'Identifier column "Id" cannot be ignored.',
            $violations[0]->getMessage()
        );
        static::assertSame(
            'columns[0]',
            $violations[0]->getPropertyPath()
        );
    }

    public function testToString(): void
    {
        static::assertSame('', $this->entity->__toString());

        $this->entity->setId(999);

        static::assertSame('999', $this->entity->__toString());
    }
}
