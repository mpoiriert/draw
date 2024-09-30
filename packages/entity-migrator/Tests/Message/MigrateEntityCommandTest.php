<?php

namespace Draw\Component\EntityMigrator\Tests\Message;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\EntityMigrator\Entity\EntityMigrationInterface;
use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

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
            null
        );

        static::expectExceptionObject(
            new UnrecoverableMessageHandlingException('Entity not found')
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
