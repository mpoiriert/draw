<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecuteCronJobMessageHandler::class)]
class ExecuteCronJobMessageHandlerTest extends TestCase
{
    private ExecuteCronJobMessageHandler $handler;

    private CronJobProcessor&MockObject $cronJobProcessor;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry
            ->expects(static::any())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($this->entityManager = $this->createMock(EntityManagerInterface::class));

        $this->handler = new ExecuteCronJobMessageHandler(
            $managerRegistry,
            $this->cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );
    }

    public function testHandleExecuteCronJobMessage(): void
    {
        $this->entityManager
            ->expects(static::never())
            ->method('flush');

        $this->cronJobProcessor
            ->expects(static::once())
            ->method('process')
            ->with($execution = $this->createCronJobExecution());

        $this->handler->handleExecuteCronJobMessage(
            new ExecuteCronJobMessage($execution)
        );

        static::assertEquals(CronJobExecution::STATE_REQUESTED, $execution->getState());
        static::assertNotNull($execution->getRequestedAt());
    }

    public function testHandleExecuteCronJobMessageWithNotExecutableExecution(): void
    {
        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->cronJobProcessor
            ->expects(static::never())
            ->method('process');

        $this->handler->handleExecuteCronJobMessage(
            new ExecuteCronJobMessage($execution = $this->createCronJobExecution(false))
        );

        static::assertEquals(CronJobExecution::STATE_SKIPPED, $execution->getState());
        static::assertNotNull($execution->getRequestedAt());
    }

    private function createCronJobExecution(bool $active = true): CronJobExecution
    {
        return new CronJobExecution(
            (new CronJob())->setActive($active),
            new \DateTimeImmutable(),
            false
        );
    }
}
