<?php

namespace Draw\Component\Console\Tests\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Core\DateTimeUtils;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Console\Entity\Execution
 */
class ExecutionTest extends TestCase
{
    private Execution $entity;

    protected function setUp(): void
    {
        $this->entity = new Execution();
    }

    public function testConstants(): void
    {
        static::assertSame(
            'initialized',
            $this->entity::STATE_INITIALIZED
        );

        static::assertSame(
            'started',
            $this->entity::STATE_STARTED
        );

        static::assertSame(
            'error',
            $this->entity::STATE_ERROR
        );

        static::assertSame(
            'terminated',
            $this->entity::STATE_TERMINATED
        );

        static::assertSame(
            'acknowledge',
            $this->entity::STATE_ACKNOWLEDGE
        );

        static::assertSame(
            'auto_acknowledge',
            $this->entity::STATE_AUTO_ACKNOWLEDGE
        );

        static::assertSame(
            [
                $this->entity::STATE_INITIALIZED,
                $this->entity::STATE_STARTED,
                $this->entity::STATE_ERROR,
                $this->entity::STATE_TERMINATED,
                $this->entity::STATE_ACKNOWLEDGE,
                $this->entity::STATE_AUTO_ACKNOWLEDGE,
            ],
            $this->entity::STATES,
        );
    }

    public function testIdMutator(): void
    {
        static::assertNull($this->entity->getId());

        static::assertSame(
            $this->entity,
            $this->entity->setId($value = rand(\PHP_INT_MIN, \PHP_INT_MAX))
        );

        static::assertSame(
            $value,
            $this->entity->getId()
        );
    }

    public function testCommandMutator(): void
    {
        static::assertNull($this->entity->getCommand());

        static::assertSame(
            $this->entity,
            $this->entity->setCommand($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getCommand()
        );
    }

    public function testCommandNameMutator(): void
    {
        static::assertNull($this->entity->getCommandName());

        static::assertSame(
            $this->entity,
            $this->entity->setCommandName($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getCommandName()
        );
    }

    public function testStateMutator(): void
    {
        static::assertNull($this->entity->getState());

        static::assertSame(
            $this->entity,
            $this->entity->setState($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getState()
        );
    }

    public function testInputMutator(): void
    {
        static::assertSame([], $this->entity->getInput());

        static::assertSame(
            $this->entity,
            $this->entity->setInput($value = [uniqid('value-')])
        );

        static::assertSame(
            $value,
            $this->entity->getInput()
        );
    }

    public function testOutputMutator(): void
    {
        static::assertSame('', $this->entity->getOutput());

        static::assertSame(
            $this->entity,
            $this->entity->setOutput($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getOutput()
        );
    }

    public function testCreatedAtMutator(): void
    {
        static::assertEqualsWithDelta(
            new DateTimeImmutable(),
            $this->entity->getCreatedAt(),
            2
        );

        static::assertSame(
            $this->entity,
            $this->entity->setCreatedAt($value = new DateTimeImmutable('- 1 days'))
        );

        static::assertEqualsWithDelta(
            $value,
            $this->entity->getCreatedAt(),
            2
        );
    }

    public function testUpdatedAtMutator(): void
    {
        static::assertTrue(
            DateTimeUtils::isSameTimestamp(
                $this->entity->getCreatedAt(),
                $this->entity->getUpdatedAt()
            )
        );

        static::assertSame(
            $this->entity,
            $this->entity->setUpdatedAt($value = new DateTimeImmutable('- 1 days'))
        );

        static::assertEqualsWithDelta(
            $value,
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testAutoAcknowledgeReasonMutator(): void
    {
        static::assertNull($this->entity->getAutoAcknowledgeReason());

        static::assertSame(
            $this->entity,
            $this->entity->setAutoAcknowledgeReason($value = uniqid())
        );

        static::assertSame(
            $value,
            $this->entity->getAutoAcknowledgeReason()
        );
    }

    public function testGetOutputHtml(): void
    {
        $this->entity->setOutput("test\n");

        static::assertSame(
            '<span style="background-color: black; color: white">test<br />
</span>',
            $this->entity->getOutputHtml()
        );
    }

    public function testGetCommandLine(): void
    {
        $this->entity->setInput([$value = uniqid('value-')]);

        static::assertSame(
            $value,
            $this->entity->getCommandLine()
        );
    }

    public function testUpdateTimestampNotSet(): void
    {
        $this->entity->updateTimestamp($this->createMock(PreUpdateEventArgs::class));

        static::assertEqualsWithDelta(
            $this->entity->getCreatedAt(),
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testUpdateTimestampAlreadySet(): void
    {
        $this->entity->setUpdatedAt($value = new DateTimeImmutable('- 1 days'));

        $this->entity->updateTimestamp($this->createMock(PreUpdateEventArgs::class));

        static::assertNotEqualsWithDelta(
            $value,
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testUpdateTimestampAlreadyChanged(): void
    {
        $this->entity->setUpdatedAt($value = new DateTimeImmutable('- 1 days'));

        $event = $this->createMock(PreUpdateEventArgs::class);

        $event->expects(static::once())
            ->method('hasChangedField')
            ->with('updatedAt')
            ->willReturn(true);

        $this->entity->updateTimestamp($event);

        static::assertEqualsWithDelta(
            $value,
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testToString(): void
    {
        static::assertSame('', (string) $this->entity);

        $this->entity->setCommandName($value = uniqid());

        static::assertSame(
            $value,
            (string) $this->entity
        );
    }
}
