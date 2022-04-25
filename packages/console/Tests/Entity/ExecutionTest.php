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

    public function setUp(): void
    {
        $this->entity = new Execution();
    }

    public function testConstants(): void
    {
        $this->assertSame(
            'initialized',
            $this->entity::STATE_INITIALIZED
        );

        $this->assertSame(
            'started',
            $this->entity::STATE_STARTED
        );

        $this->assertSame(
            'error',
            $this->entity::STATE_ERROR
        );

        $this->assertSame(
            'terminated',
            $this->entity::STATE_TERMINATED
        );

        $this->assertSame(
            'acknowledge',
            $this->entity::STATE_ACKNOWLEDGE
        );

        $this->assertSame(
            'auto_acknowledge',
            $this->entity::STATE_AUTO_ACKNOWLEDGE
        );

        $this->assertSame(
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
        $this->assertNull($this->entity->getId());

        $this->assertSame(
            $this->entity,
            $this->entity->setId($value = rand(PHP_INT_MIN, PHP_INT_MAX))
        );

        $this->assertSame(
            $value,
            $this->entity->getId()
        );
    }

    public function testCommandMutator(): void
    {
        $this->assertNull($this->entity->getCommand());

        $this->assertSame(
            $this->entity,
            $this->entity->setCommand($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getCommand()
        );
    }

    public function testCommandNameMutator(): void
    {
        $this->assertNull($this->entity->getCommandName());

        $this->assertSame(
            $this->entity,
            $this->entity->setCommandName($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getCommandName()
        );
    }

    public function testStateMutator(): void
    {
        $this->assertNull($this->entity->getState());

        $this->assertSame(
            $this->entity,
            $this->entity->setState($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getState()
        );
    }

    public function testInputMutator(): void
    {
        $this->assertSame([], $this->entity->getInput());

        $this->assertSame(
            $this->entity,
            $this->entity->setInput($value = [uniqid('value-')])
        );

        $this->assertSame(
            $value,
            $this->entity->getInput()
        );
    }

    public function testOutputMutator(): void
    {
        $this->assertSame('', $this->entity->getOutput());

        $this->assertSame(
            $this->entity,
            $this->entity->setOutput($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getOutput()
        );
    }

    public function testCreatedAtMutator(): void
    {
        $this->assertEqualsWithDelta(
            new DateTimeImmutable(),
            $this->entity->getCreatedAt(),
            2
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setCreatedAt($value = new DateTimeImmutable('- 1 days'))
        );

        $this->assertEqualsWithDelta(
            $value,
            $this->entity->getCreatedAt(),
            2
        );
    }

    public function testUpdatedAtMutator(): void
    {
        $this->assertTrue(
            DateTimeUtils::isSameTimestamp(
                $this->entity->getCreatedAt(),
                $this->entity->getUpdatedAt()
            )
        );

        $this->assertSame(
            $this->entity,
            $this->entity->setUpdatedAt($value = new DateTimeImmutable('- 1 days'))
        );

        $this->assertEqualsWithDelta(
            $value,
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testAutoAcknowledgeReasonMutator(): void
    {
        $this->assertNull($this->entity->getAutoAcknowledgeReason());

        $this->assertSame(
            $this->entity,
            $this->entity->setAutoAcknowledgeReason($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->entity->getAutoAcknowledgeReason()
        );
    }

    public function testGetOutputHtml(): void
    {
        $this->entity->setOutput("test\n");

        $this->assertSame(
            '<span style="background-color: black; color: white">test<br />
</span>',
            $this->entity->getOutputHtml()
        );
    }

    public function testGetCommandLine(): void
    {
        $this->entity->setInput([$value = uniqid('value-')]);

        $this->assertSame(
            $value,
            $this->entity->getCommandLine()
        );
    }

    public function testUpdateTimestampNotSet(): void
    {
        $this->entity->updateTimestamp($this->createMock(PreUpdateEventArgs::class));

        $this->assertEqualsWithDelta(
            $this->entity->getCreatedAt(),
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testUpdateTimestampAlreadySet(): void
    {
        $this->entity->setUpdatedAt($value = new DateTimeImmutable('- 1 days'));

        $this->entity->updateTimestamp($this->createMock(PreUpdateEventArgs::class));

        $this->assertNotEqualsWithDelta(
            $value,
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testUpdateTimestampAlreadyChanged(): void
    {
        $this->entity->setUpdatedAt($value = new DateTimeImmutable('- 1 days'));

        $event = $this->createMock(PreUpdateEventArgs::class);

        $event->expects($this->once())
            ->method('hasChangedField')
            ->with('updatedAt')
            ->willReturn(true);

        $this->entity->updateTimestamp($event);

        $this->assertEqualsWithDelta(
            $value,
            $this->entity->getUpdatedAt(),
            2
        );
    }

    public function testToString(): void
    {
        $this->assertSame('', (string) $this->entity);

        $this->entity->setCommandName($value = uniqid());

        $this->assertSame(
            $value,
            (string) $this->entity
        );
    }
}
