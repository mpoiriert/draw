<?php

namespace Draw\Component\Messenger\Tests\Broker\Command;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\Broker\Command\StartMessengerBrokerCommand;
use Draw\Component\Messenger\Broker\Event\BrokerStartedEvent;
use Draw\Component\Messenger\Counter\CpuCounter;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(StartMessengerBrokerCommand::class)]
class StartMessengerBrokerCommandTest extends TestCase
{
    use CommandTestTrait;

    private ProcessFactoryInterface $processFactory;

    private EventDispatcher $eventDispatcher;

    private CpuCounter&MockObject $cpuCounter;

    private string $consolePath;

    protected function setUp(): void
    {
        $this->command = new StartMessengerBrokerCommand(
            $this->consolePath = uniqid('console-path-'),
            $this->processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->eventDispatcher = new EventDispatcher(),
            $this->cpuCounter = $this->createMock(CpuCounter::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:messenger:start-broker';
    }

    public static function provideTestArgument(): iterable
    {
        return [];
    }

    public static function provideTestOption(): iterable
    {
        yield [
            'context',
            null,
            InputOption::VALUE_REQUIRED,
            'default',
        ];

        yield [
            'concurrent',
            null,
            InputOption::VALUE_REQUIRED,
            1,
        ];

        yield [
            'processes-per-core',
            null,
            InputOption::VALUE_REQUIRED,
            1,
        ];

        yield [
            'minimum-processes',
            null,
            InputOption::VALUE_REQUIRED,
            1,
        ];

        yield [
            'maximum-processes',
            null,
            InputOption::VALUE_REQUIRED,
            null,
        ];

        yield [
            'timeout',
            null,
            InputOption::VALUE_REQUIRED,
            10,
        ];
    }

    public function testExecuteInvalidConcurrent(): void
    {
        $concurrent = random_int(\PHP_INT_MIN, 0);
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Concurrent value ['.$concurrent.'] is invalid. Must be 1 or greater');

        $this->execute(['--concurrent' => $concurrent]);
    }

    public function testExecuteInvalidTimeout(): void
    {
        $timeout = random_int(\PHP_INT_MIN, -1);
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Timeout value ['.$timeout.'] is invalid. Must be 0 or greater');

        $this->execute(['--timeout' => $timeout]);
    }

    public function testExecuteInvalidProcessesPerCoreWithAutoConcurrent(): void
    {
        $processesPerCore = random_int(\PHP_INT_MIN, 0);
        $this->expectExceptionObject(new InvalidOptionException(sprintf(
            'Processes per core value [%f] is invalid. Must be greater than 0',
            $processesPerCore
        )));

        $this->execute([
            '--concurrent' => 'auto',
            '--processes-per-core' => $processesPerCore,
        ]);
    }

    public function testExecuteInvalidMinimumProcessesWithAutoConcurrent(): void
    {
        $minProcesses = random_int(\PHP_INT_MIN, 0);
        $this->expectExceptionObject(new InvalidOptionException(sprintf(
            'Minimum processes value [%d] is invalid. Must be greater than 0',
            $minProcesses
        )));

        $this->execute([
            '--concurrent' => 'auto',
            '--minimum-processes' => $minProcesses,
        ]);
    }

    public function testExecuteInvalidMaximumProcessesWithAutoConcurrent(): void
    {
        $maxProcesses = random_int(\PHP_INT_MIN, 0);
        $this->expectExceptionObject(new InvalidOptionException(sprintf(
            'Maximum processes value [%d] is invalid. Must be greater than 0',
            $maxProcesses
        )));

        $this->execute([
            '--concurrent' => 'auto',
            '--maximum-processes' => $maxProcesses,
        ]);
    }

    public function testExecute(): void
    {
        $concurrent = random_int(1, 10);
        $timeout = random_int(1, 10);
        $context = uniqid('context-');

        $this->eventDispatcher->addListener(
            BrokerStartedEvent::class,
            function (BrokerStartedEvent $event) use ($concurrent, $timeout, $context): void {
                $this->assertSame(
                    $context,
                    $event->getBroker()->getContext()
                );

                $this->assertSame(
                    $concurrent,
                    $event->getConcurrent()
                );

                $this->assertSame(
                    $timeout,
                    $event->getTimeout()
                );

                $broker = $event->getBroker();

                $this->assertSame(
                    $this->processFactory,
                    ReflectionAccessor::getPropertyValue($broker, 'processFactory')
                );

                $this->assertSame(
                    $this->eventDispatcher,
                    ReflectionAccessor::getPropertyValue($broker, 'eventDispatcher')
                );

                $this->assertSame(
                    $this->consolePath,
                    ReflectionAccessor::getPropertyValue($broker, 'consolePath')
                );

                $broker->stop();
            }
        );

        $this->execute([
            '--context' => $context,
            '--concurrent' => $concurrent,
            '--timeout' => $timeout,
            '--processes-per-core' => random_int(\PHP_INT_MIN, 0),
            '--minimum-processes' => random_int(\PHP_INT_MIN, 0),
        ])->test(
            CommandDataTester::create(
                0,
                [
                    '[OK] Broker starting.',
                    '! [NOTE] Concurrency '.$concurrent,
                    '! [NOTE] Timeout '.$timeout,
                    '[OK] Broker stopped. ',
                ]
            )
        );
    }

    /**
     * @dataProvider provideDataForTestExecuteWithAutoConcurrent
     */
    public function testExecuteWithAutoConcurrent(
        int $numCpus,
        float $processesPerCore,
        int $minProcesses,
        ?int $maxProcesses,
        int $concurrent
    ): void {
        $this
            ->cpuCounter
            ->method('count')
            ->willReturn($numCpus);

        $this->eventDispatcher->addListener(
            BrokerStartedEvent::class,
            function (BrokerStartedEvent $event) use ($concurrent): void {
                static::assertSame($concurrent, $event->getConcurrent());

                $broker = $event->getBroker();
                $broker->stop();
            }
        );

        $this
            ->execute(
                array_filter(
                    [
                        '--concurrent' => 'auto',
                        '--processes-per-core' => $processesPerCore,
                        '--minimum-processes' => $minProcesses,
                        '--maximum-processes' => $maxProcesses,
                    ],
                    static fn ($value) => null !== $value
                )
            )
            ->test(CommandDataTester::create(
                0,
                [
                    '[OK] Broker starting.',
                    sprintf('! [NOTE] Concurrency %d', $concurrent),
                    '! [NOTE] Timeout 10',
                    '[OK] Broker stopped. ',
                ]
            ));
    }

    public static function provideDataForTestExecuteWithAutoConcurrent(): iterable
    {
        yield 'integer multiplier' => [
            'numCpus' => 4,
            'processesPerCore' => 1.0,
            'minProcesses' => 2,
            'maxProcesses' => null,
            'concurrent' => 4,
        ];

        yield 'float multiplier' => [
            'numCpus' => 4,
            'processesPerCore' => 0.8,
            'minProcesses' => 1,
            'maxProcesses' => null,
            'concurrent' => 3,
        ];

        yield 'minimum processes' => [
            'numCpus' => 2,
            'processesPerCore' => 0.8,
            'minProcesses' => 5,
            'maxProcesses' => null,
            'concurrent' => 5,
        ];

        yield 'maximum processes' => [
            'numCpus' => 2,
            'processesPerCore' => 5,
            'minProcesses' => 1,
            'maxProcesses' => 1,
            'concurrent' => 1,
        ];
    }
}
