<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Event\PostCronJobExecutionEvent;
use Draw\Component\CronJob\Event\PreCronJobExecutionEvent;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Component\Tester\DoubleTrait;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(CronJobProcessor::class)]
class CronJobProcessorTest extends TestCase
{
    use DoubleTrait;

    #[DataProvider('provideQueueCases')]
    public function testQueue(bool $force): void
    {
        $cronJobProcessor = new CronJobProcessor(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            new ParameterBag([
                'kernel.cache_dir' => '/var/cache',
            ]),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(ProcessFactoryInterface::class),
            $messageBus = $this->createMock(MessageBusInterface::class)
        );

        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($entityManager = $this->createMock(EntityManagerInterface::class))
        ;

        $cronJob = $this->createMock(CronJob::class);
        $cronJob
            ->expects($this->once())
            ->method('newExecution')
            ->with($force)
            ->willReturn($execution = $this->createCronJobExecution())
        ;

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($execution)
        ;

        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($message = new ExecuteCronJobMessage($execution))
            ->willReturn(new Envelope($message, []))
        ;

        $cronJobProcessor->queue($cronJob, $force);
    }

    public static function provideQueueCases(): iterable
    {
        yield 'normal' => ['force' => false];

        yield 'forced' => ['force' => true];
    }

    #[DataProvider('provideProcessCases')]
    public function testProcess(
        string $command,
        ?string $overwrittenCommand,
        string $expectedProcessCommand,
    ): void {
        $cronJobProcessor = new CronJobProcessor(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            new ParameterBag([
                'kernel.cache_dir' => '/var/cache',
            ]),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->createStub(MessageBusInterface::class)
        );

        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($entityManager = $this->createMock(EntityManagerInterface::class))
        ;

        $returnedPreCronJobExecutionEvent = new PreCronJobExecutionEvent(
            $execution = $this->createCronJobExecution($command)
        );

        if (null !== $overwrittenCommand) {
            $returnedPreCronJobExecutionEvent->setCommand($overwrittenCommand);
        }

        $execution->getCronJob()->setExecutionTimeout($executionTimeout = random_int(1, 100));

        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [
                        new PreCronJobExecutionEvent($execution),
                    ],
                    [
                        $postExecutionEvent = new PostCronJobExecutionEvent($execution),
                    ]
                )
            )
            ->willReturnOnConsecutiveCalls(
                $returnedPreCronJobExecutionEvent,
                $postExecutionEvent
            )
        ;

        $entityManager
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn(
                $connection = $this->createMock(Connection::class)
            )
        ;

        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $processFactory
            ->expects($this->once())
            ->method('createFromShellCommandLine')
            ->with(
                $expectedProcessCommand,
                null,
                null,
                null,
                $executionTimeout,
            )
            ->willReturn($process = $this->createMock(Process::class))
        ;

        $process
            ->expects($this->once())
            ->method('mustRun')
        ;

        $cronJobProcessor->process($execution);

        $this->assertSame(CronJobExecution::STATE_TERMINATED, $execution->getState());
        $this->assertNotNull($execution->getExecutionStartedAt());
        $this->assertNotNull($execution->getExecutionEndedAt());
        $this->assertSame(
            $execution->getExecutionEndedAt()->getTimestamp() - $execution->getExecutionStartedAt()->getTimestamp(),
            $execution->getExecutionDelay()
        );
        $this->assertSame(0, $execution->getExitCode());
        $this->assertNull($execution->getError());
    }

    public static function provideProcessCases(): iterable
    {
        yield 'original command' => [
            'command' => 'bin/console draw:test:successfully',
            'overwrittenCommand' => null,
            'expectedProcessCommand' => 'bin/console draw:test:successfully',
        ];

        yield 'overwritten command' => [
            'command' => $command = 'ls -lah %kernel.cache_dir%',
            'overwrittenCommand' => \sprintf('%s | wc', $command),
            'expectedProcessCommand' => 'ls -lah /var/cache | wc',
        ];
    }

    public function testProcessWithError(): void
    {
        $cronJobProcessor = new CronJobProcessor(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            new ParameterBag([
                'kernel.cache_dir' => '/var/cache',
            ]),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->createStub(MessageBusInterface::class)
        );

        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($entityManager = $this->createMock(EntityManagerInterface::class))
        ;

        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [
                        $preExecutionEvent = new PreCronJobExecutionEvent(
                            $execution = $this->createCronJobExecution('echo 12345 > %kernel.cache_dir%/crontab.out')
                        ),
                    ],
                    [
                        $postExecutionEvent = new PostCronJobExecutionEvent($execution),
                    ]
                )
            )
            ->willReturnOnConsecutiveCalls($preExecutionEvent, $postExecutionEvent)
        ;

        $entityManager
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn(
                $connection = $this->createMock(Connection::class)
            )
        ;

        $connection
            ->expects($this->once())
            ->method('close')
        ;

        $process = $this->createMock(Process::class);
        $process
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn($exitCode = 127)
        ;

        $process
            ->expects($this->once())
            ->method('mustRun')
            ->willThrowException(
                new \Exception(
                    'Exception while processing command.',
                    previous: new \Exception('Nested exception.')
                )
            )
        ;

        $process
            ->expects($this->once())
            ->method('getOutput')
        ;

        $process
            ->expects($this->once())
            ->method('getErrorOutput')
        ;

        $processFactory
            ->expects($this->once())
            ->method('createFromShellCommandLine')
            ->with(
                'echo 12345 > /var/cache/crontab.out',
                null,
                null,
                null,
                $execution->getCronJob()->getExecutionTimeout()
            )
            ->willReturn($process)
        ;

        $cronJobProcessor->process($execution);

        $this->assertSame(CronJobExecution::STATE_ERRORED, $execution->getState());
        $this->assertNotNull($execution->getExecutionStartedAt());
        $this->assertNotNull($execution->getExecutionEndedAt());
        $this->assertNotNull($execution->getExecutionDelay());
        $this->assertSame($exitCode, $execution->getExitCode());
        $this->assertNotNull($execution->getError());
    }

    public function testProcessWithInactiveCronJob(): void
    {
        $cronJobProcessor = new CronJobProcessor(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            new ParameterBag([
                'kernel.cache_dir' => '/var/cache',
            ]),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->createStub(MessageBusInterface::class)
        );

        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($entityManager = $this->createMock(EntityManagerInterface::class))
        ;

        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch')
        ;

        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $processFactory
            ->expects($this->never())
            ->method('createFromShellCommandLine')
        ;

        $cronJobProcessor->process(
            $execution = (new CronJob())
                ->setActive(false)
                ->newExecution()
        );

        $this->assertSame(CronJobExecution::STATE_SKIPPED, $execution->getState());
    }

    public function testProcessWithCancelledExecution(): void
    {
        $cronJobProcessor = new CronJobProcessor(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            new ParameterBag([
                'kernel.cache_dir' => '/var/cache',
            ]),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->createStub(MessageBusInterface::class)
        );

        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($entityManager = $this->createMock(EntityManagerInterface::class))
        ;

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                new PreCronJobExecutionEvent($execution = $this->createCronJobExecution())
            )
            ->willReturn(
                new PreCronJobExecutionEvent($execution, true)
            )
        ;

        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $processFactory
            ->expects($this->never())
            ->method('createFromShellCommandLine')
        ;

        $cronJobProcessor->process($execution);

        $this->assertSame(CronJobExecution::STATE_SKIPPED, $execution->getState());
    }

    private function createCronJobExecution(string $command = 'bin/console draw:test:execute'): CronJobExecution
    {
        return new CronJobExecution(
            (new CronJob())
                ->setActive(true)
                ->setCommand($command),
            new \DateTimeImmutable(),
            false
        );
    }
}
