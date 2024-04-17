<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;

#[CoversClass(CronJobProcessor::class)]
class CronJobProcessorTest extends TestCase
{
    private CronJobProcessor $cronJobProcessor;

    private ManagerRegistry&MockObject $managerRegistry;

    private ProcessFactoryInterface&MockObject $processFactory;

    private MessageBusInterface&MockObject $messageBus;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry
            ->expects(static::any())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($this->entityManager = $this->createMock(EntityManagerInterface::class));

        $this->cronJobProcessor = new CronJobProcessor(
            $this->managerRegistry,
            $this->processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->messageBus = $this->createMock(MessageBusInterface::class)
        );
    }

    #[DataProvider('provideDataForTestQueue')]
    public function testQueue(bool $force): void
    {
        $cronJob = $this->createMock(CronJob::class);
        $cronJob
            ->expects(static::any())
            ->method('newExecution')
            ->with($force)
            ->willReturn($execution = $this->createExecution());

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with($execution);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->messageBus
            ->expects(static::once())
            ->method('dispatch')
            ->with($message = new ExecuteCronJobMessage($execution))
            ->willReturn(new Envelope($message, []));

        $this->cronJobProcessor->queue($cronJob, $force);
    }

    public static function provideDataForTestQueue(): iterable
    {
        yield 'not forced' => ['$force' => false];
        yield 'forced' => ['$force' => true];
    }

    public function testProcess(): void
    {
        $this->entityManager
            ->expects(static::exactly(2))
            ->method('flush');

        $this->processFactory
            ->expects(static::once())
            ->method('create')
            ->with(
                [$command = 'draw:test:successfully'],
                null,
                null,
                null,
                1800
            )
            ->willReturn($process = $this->createMock(Process::class));

        $process
            ->expects(static::once())
            ->method('mustRun');

        $this->cronJobProcessor->process($execution = $this->createExecution($command));

        static::assertNotNull($execution->getExecutionStartedAt());
        static::assertNotNull($execution->getExecutionEndedAt());
        static::assertEquals(
            $execution->getExecutionEndedAt()->getTimestamp() - $execution->getExecutionStartedAt()->getTimestamp(),
            $execution->getExecutionDelay()
        );
        static::assertEquals(0, $execution->getExitCode());
        static::assertNull($execution->getError());
    }

    public function testProcessWithError(): void
    {
        $this->entityManager
            ->expects(static::exactly(2))
            ->method('flush');

        $process = $this->createMock(Process::class);
        $process
            ->expects(static::any())
            ->method('getExitCode')
            ->willReturn($exitCode = 127);
        $process
            ->expects(static::any())
            ->method('mustRun')
            ->willThrowException(
                new \Exception(
                    'Exception while processing command.',
                    previous: new \Exception('Nested exception.')
                )
            );

        $this->processFactory
            ->expects(static::once())
            ->method('create')
            ->with(
                [$command = 'draw:test:failure'],
                null,
                null,
                null,
                1800
            )
            ->willReturn($process);

        $this->cronJobProcessor->process($execution = $this->createExecution($command));

        static::assertNotNull($execution->getExecutionStartedAt());
        static::assertNull($execution->getExecutionEndedAt());
        static::assertNull($execution->getExecutionDelay());
        static::assertEquals($exitCode, $execution->getExitCode());
        static::assertNotNull($execution->getError());
    }

    private function createExecution(string $command = 'draw:test:execute'): CronJobExecution
    {
        return (new CronJobExecution())
            ->setCronJob(
                (new CronJob())->setCommand($command)
            );
    }
}
