<?php

namespace Draw\Component\EntityMigrator\Tests\Message;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Exception\ObjectNotFoundException;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MigrateEntityCommand::class)]
class MigrateEntityCommandTest extends TestCase
{
    private MigrateEntityCommand $object;

    private EntityMigrationInterface $entityMigration;

    protected function setUp(): void
    {
        $this->object = new MigrateEntityCommand(
            $this->entityMigration = $this->createMock(EntityMigrationInterface::class)
        );
    }

    public function testGetEntity(): void
    {
        static::assertSame(
            $this->entityMigration,
            $this->object->getEntity()
        );

        ReflectionAccessor::setPropertyValue(
            $this->object,
            'entity',
            $stamp = new PropertyReferenceStamp('entity', EntityMigrationInterface::class, ['id' => 1])
        );

        static::expectExceptionObject(
            new ObjectNotFoundException(EntityMigrationInterface::class, $stamp)
        );

        $this->object->getEntity();
    }

    public function testGetPropertiesWithDoctrineObject(): void
    {
        static::assertSame(
            ['entity'],
            $this->object->getPropertiesWithDoctrineObject()
        );
    }
}
