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
use Draw\Component\Tester\MockTrait;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
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
    use MockTrait;

    private CronJobProcessor $cronJobProcessor;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private ProcessFactoryInterface&MockObject $processFactory;

    private MessageBusInterface&MockObject $messageBus;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry
            ->expects(static::any())
            ->method('getManagerForClass')
            ->with(CronJobExecution::class)
            ->willReturn($this->entityManager = $this->createMock(EntityManagerInterface::class))
        ;

        $this->cronJobProcessor = new CronJobProcessor(
            $managerRegistry,
            new ParameterBag([
                'kernel.cache_dir' => '/var/cache',
            ]),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class),
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
            ->willReturn($execution = $this->createCronJobExecution())
        ;

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with($execution)
        ;

        $this->entityManager
            ->expects(static::once())
            ->method('flush')
        ;

        $this->messageBus
            ->expects(static::once())
            ->method('dispatch')
            ->with($message = new ExecuteCronJobMessage($execution))
            ->willReturn(new Envelope($message, []))
        ;

        $this->cronJobProcessor->queue($cronJob, $force);
    }

    public static function provideDataForTestQueue(): iterable
    {
        yield 'normal' => ['force' => false];

        yield 'forced' => ['force' => true];
    }

    #[DataProvider('provideDataForTestProcess')]
    public function testProcess(
        string $command,
        ?string $overwrittenCommand,
        string $expectedProcessCommand,
    ): void {
        $returnedPreCronJobExecutionEvent = new PreCronJobExecutionEvent(
            $execution = $this->createCronJobExecution($command)
        );

        if (null !== $overwrittenCommand) {
            $returnedPreCronJobExecutionEvent->setCommand($overwrittenCommand);
        }

        $execution->getCronJob()->setExecutionTimeout($executionTimeout = random_int(1, 100));

        $this->eventDispatcher
            ->expects(static::exactly(2))
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

        $this->entityManager
            ->expects(static::exactly(2))
            ->method('flush')
        ;

        $this->entityManager
            ->expects(static::once())
            ->method('getConnection')
            ->willReturn(
                $connection = $this->createMock(Connection::class)
            )
        ;

        $connection
            ->expects(static::once())
            ->method('close')
        ;

        $this->processFactory
            ->expects(static::once())
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
            ->expects(static::once())
            ->method('mustRun')
        ;

        $this->cronJobProcessor->process($execution);

        static::assertSame(CronJobExecution::STATE_TERMINATED, $execution->getState());
        static::assertNotNull($execution->getExecutionStartedAt());
        static::assertNotNull($execution->getExecutionEndedAt());
        static::assertSame(
            $execution->getExecutionEndedAt()->getTimestamp() - $execution->getExecutionStartedAt()->getTimestamp(),
            $execution->getExecutionDelay()
        );
        static::assertSame(0, $execution->getExitCode());
        static::assertNull($execution->getError());
    }

    public static function provideDataForTestProcess(): iterable
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
        $this->eventDispatcher
            ->expects(static::exactly(2))
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

        $this->entityManager
            ->expects(static::exactly(2))
            ->method('flush')
        ;

        $this->entityManager
            ->expects(static::once())
            ->method('getConnection')
            ->willReturn(
                $connection = $this->createMock(Connection::class)
            )
        ;

        $connection
            ->expects(static::once())
            ->method('close')
        ;

        $process = $this->createMock(Process::class);
        $process
            ->expects(static::any())
            ->method('getExitCode')
            ->willReturn($exitCode = 127)
        ;
        $process
            ->expects(static::any())
            ->method('mustRun')
            ->willThrowException(
                new \Exception(
                    'Exception while processing command.',
                    previous: new \Exception('Nested exception.')
                )
            )
        ;

        $this->processFactory
            ->expects(static::once())
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

        $this->cronJobProcessor->process($execution);

        static::assertSame(CronJobExecution::STATE_ERRORED, $execution->getState());
        static::assertNotNull($execution->getExecutionStartedAt());
        static::assertNotNull($execution->getExecutionEndedAt());
        static::assertNotNull($execution->getExecutionDelay());
        static::assertSame($exitCode, $execution->getExitCode());
        static::assertNotNull($execution->getError());
    }

    public function testProcessWithInactiveCronJob(): void
    {
        $this->eventDispatcher
            ->expects(static::never())
            ->method('dispatch')
        ;

        $this->entityManager
            ->expects(static::once())
            ->method('flush')
        ;

        $this->processFactory
            ->expects(static::never())
            ->method('createFromShellCommandLine')
        ;

        $this->cronJobProcessor->process(
            $execution = (new CronJob())
                ->setActive(false)
                ->newExecution()
        );

        static::assertSame(CronJobExecution::STATE_SKIPPED, $execution->getState());
    }

    public function testProcessWithCancelledExecution(): void
    {
        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                new PreCronJobExecutionEvent($execution = $this->createCronJobExecution())
            )
            ->willReturn(
                new PreCronJobExecutionEvent($execution, true)
            )
        ;

        $this->entityManager
            ->expects(static::once())
            ->method('flush')
        ;

        $this->processFactory
            ->expects(static::never())
            ->method('createFromShellCommandLine')
        ;

        $this->cronJobProcessor->process($execution);

        static::assertSame(CronJobExecution::STATE_SKIPPED, $execution->getState());
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
