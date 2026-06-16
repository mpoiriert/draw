<?php

namespace Draw\Component\Console\Tests\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Schema\MySQLSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Console\Event\CommandErrorEvent;
use Draw\Component\Console\Event\LoadExecutionIdEvent;
use Draw\Component\Console\EventListener\CommandFlowListener;
use Draw\Component\Console\Output\BufferedConsoleOutput;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Tester\DoctrineOrmTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(CommandFlowListener::class)]
class CommandFlowListenerTest extends TestCase
{
    use DoctrineOrmTrait;

    private static EntityManagerInterface $entityManager;

    private ?Execution $execution = null;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = static::setUpMySqlWithAttributeDriver(
            [\dirname((new \ReflectionClass(Execution::class))->getFileName())],
        );
    }

    protected function setUp(): void
    {
        if ($this->execution) {
            self::$entityManager->refresh($this->execution);
        }
    }

    public function testGetSubscribedEvents(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        static::assertSame(
            [
                LoadExecutionIdEvent::class => [
                    ['checkIgnoredCommands'],
                    ['checkHelp'],
                    ['checkTableExist'],
                    ['loadIdFromInput', -10],
                    ['generateFromDatabase', -10],
                ],
                ConsoleCommandEvent::class => [
                    ['configureOptions', 1],
                    ['logCommandStart', 0],
                ],
                Event\ConsoleTerminateEvent::class => ['logCommandTerminate'],
                Event\ConsoleErrorEvent::class => ['logCommandError'],
            ],
            $listener::getSubscribedEvents()
        );
    }

    public function testConfigureOptions(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $commandEvent = $this->createCommandEvent();
        $listener->configureOptions($commandEvent);

        $command = $commandEvent->getCommand();

        $option = $command->getDefinition()->getOption($listener::OPTION_IGNORE);

        static::assertSame(
            $listener::OPTION_IGNORE,
            $option->getName()
        );

        static::assertNull(
            $option->getShortcut()
        );

        static::assertFalse(
            $option->isValueRequired()
        );

        static::assertSame(
            'Flag to ignore login of the execution to the databases.',
            $option->getDescription()
        );

        static::assertFalse(
            $option->getDefault()
        );

        $option = $command->getDefinition()->getOption($listener::OPTION_EXECUTION_ID);

        static::assertSame(
            $listener::OPTION_EXECUTION_ID,
            $option->getName()
        );

        static::assertNull(
            $option->getShortcut()
        );

        static::assertTrue(
            $option->isValueRequired()
        );

        static::assertNull(
            $option->getDefault()
        );

        static::assertSame(
            'The existing execution id of the command. Use internally by draw/console.',
            $option->getDescription()
        );
    }

    public function testCheckIgnoredCommandsIgnored(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn('help')
            ->seal()
        ;

        $listener->checkIgnoredCommands($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testCheckIgnoredCommandsNotIgnored(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn(uniqid('command-'))
            ->seal()
        ;

        $listener->checkIgnoredCommands($event);

        static::assertNull($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testCheckHelpIgnored(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            static::createStub(Command::class),
            $input = $this->createMock(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with('help')
            ->willReturn(true)
        ;

        $input
            ->expects(static::once())
            ->method('getOption')
            ->with('help')
            ->willReturn(true)
            ->seal()
        ;

        $listener->checkHelp($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testCheckHelpNotIgnored(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            static::createStub(Command::class),
            $input = $this->createMock(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with('help')
            ->willReturn(false)
            ->seal()
        ;

        $listener->checkHelp($event);

        static::assertNull($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testCheckTableExistIgnoredTableDoesNotExists(): void
    {
        $listener = new CommandFlowListener(
            $connection = $this->createMock(Connection::class),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            static::createStub(Command::class),
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $connection
            ->expects(static::once())
            ->method('createSchemaManager')
            ->willReturn($schemaManager = $this->createMock(MySQLSchemaManager::class))
            ->seal()
        ;

        $schemaManager
            ->expects(static::once())
            ->method('tablesExist')
            ->with(['command__execution'])
            ->willReturn(false)
            ->seal()
        ;

        $listener->checkTableExist($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testCheckTableExistIgnoredException(): void
    {
        $listener = new CommandFlowListener(
            $connection = $this->createMock(Connection::class),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            static::createStub(Command::class),
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $connection
            ->expects(static::once())
            ->method('createSchemaManager')
            ->willThrowException(new ConnectionException())
            ->seal()
        ;

        $listener->checkTableExist($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testLoadIdFromInputNotFound(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            static::createStub(Command::class),
            $input = $this->createMock(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with($listener::OPTION_EXECUTION_ID)
            ->willReturn(false)
            ->seal()
        ;

        $listener->loadIdFromInput($event);

        static::assertNull($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testLoadIdFromInputExists(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            static::createStub(Command::class),
            $this->createOptionExecutionIdInput($listener, $id = uniqid('id-')),
            static::createStub(OutputInterface::class)
        );

        $listener->loadIdFromInput($event);

        static::assertSame($id, $event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabaseIgnoredException(): void
    {
        $listener = new CommandFlowListener(
            $connection = $this->createMock(PrimaryReadReplicaConnection::class),
            static::createStub(EventDispatcherInterface::class),
            $logger = $this->createMock(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn(uniqid('command-'))
            ->seal()
        ;

        $input
            ->expects(static::once())
            ->method('getArguments')
            ->willReturn([])
        ;

        $input
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn([])
            ->seal()
        ;

        $connection
            ->expects(static::once())
            ->method('isConnectedToPrimary')
            ->willReturn(false)
        ;

        $connection
            ->expects(static::once())
            ->method('insert')
            ->willThrowException($error = new \Exception())
        ;

        $connection
            ->expects(static::once())
            ->method('ensureConnectedToReplica')
            ->seal()
        ;

        $logger
            ->expects(static::once())
            ->method('error')
            ->with(
                'Command flow listener error while generating execution id',
                ['error' => $error]
            )
            ->seal()
        ;

        $listener->generateFromDatabase($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabase(): void
    {
        $listener = new CommandFlowListener(
            $connection = $this->createMock(PrimaryReadReplicaConnection::class),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn($commandName = uniqid('command-'))
            ->seal()
        ;

        $input
            ->expects(static::once())
            ->method('getArguments')
            ->willReturn(['keyName' => 'keyValue'])
        ;

        $input
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn(['null' => null, 'zero' => 0, 'false' => false, 'other' => 'value'])
            ->seal()
        ;

        $connection
            ->expects(static::once())
            ->method('isConnectedToPrimary')
            ->willReturn(false)
        ;

        $connection
            ->expects(static::once())
            ->method('insert')
            ->with(
                'command__execution',
                static::callback(function (array $arguments) use ($commandName) {
                    $this->assertCount(7, $arguments);

                    $this->assertIsString($arguments['id']);

                    $this->assertSame(
                        $commandName,
                        $arguments['command_name']
                    );

                    $this->assertEqualsWithDelta(
                        new \DateTimeImmutable(),
                        new \DateTimeImmutable($arguments['created_at']),
                        2
                    );

                    $this->assertEqualsWithDelta(
                        new \DateTimeImmutable(),
                        new \DateTimeImmutable($arguments['updated_at']),
                        2
                    );

                    $this->assertSame(
                        '',
                        $arguments['output']
                    );

                    $this->assertSame(
                        Execution::STATE_STARTED,
                        $arguments['state']
                    );

                    $this->assertSame(
                        json_encode([
                            'keyName' => 'keyValue',
                            '--zero' => 0,
                            '--other' => 'value',
                        ]),
                        $arguments['input']
                    );

                    return true;
                })
            )
        ;

        $connection
            ->expects(static::once())
            ->method('ensureConnectedToReplica')
            ->seal()
        ;

        $listener->generateFromDatabase($event);

        static::assertIsString($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabaseReal(): Execution
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn(uniqid('command-'))
            ->seal()
        ;

        $input
            ->expects(static::once())
            ->method('getArguments')
            ->willReturn([])
        ;

        $input
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn([])
            ->seal()
        ;

        $listener->generateFromDatabase($event);

        static::assertNotNull($id = $event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());

        $this->execution = self::$entityManager->find(Execution::class, $id);

        return $this->execution;
    }

    public function testLogCommandStartNoExecutionId(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnArgument(0)
            ->seal()
        ;

        $event = new ConsoleCommandEvent(
            $command = $this->createMock(Command::class),
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::never())
            ->method('getDefinition')
            ->seal()
        ;

        $listener->logCommandStart($event);
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandStart(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(static function (LoadExecutionIdEvent $event) use ($execution) {
                    $event->setExecutionId($execution->getId());

                    return true;
                })
            )
            ->willReturnArgument(0)
            ->seal()
        ;

        $event = new ConsoleCommandEvent(
            $command = $this->createMock(Command::class),
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getDefinition')
            ->willReturn($definition = new InputDefinition())
            ->seal()
        ;

        $definition->addOption(
            $option = new InputOption($listener::OPTION_EXECUTION_ID, null, InputOption::VALUE_REQUIRED)
        );

        $execution->setState(uniqid('state-'));
        self::$entityManager->flush();

        $listener->logCommandStart($event);

        static::assertSame($execution->getId(), $option->getDefault());

        self::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_STARTED, $execution->getState());
    }

    public function testLogCommandTerminateReplication(): void
    {
        $listener = new CommandFlowListener(
            $connection = $this->createMock(PrimaryReadReplicaConnection::class),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $connection
            ->expects(static::once())
            ->method('isConnectedToPrimary')
            ->willReturn(false)
        ;

        $connection
            ->expects(static::once())
            ->method('ensureConnectedToReplica')
        ;

        $connection
            ->expects(static::once())
            ->method('executeStatement')
            ->seal()
        ;

        $event = new Event\ConsoleTerminateEvent(
            static::createStub(Command::class),
            $this->createOptionExecutionIdInput($listener, uniqid('id-')),
            static::createStub(OutputInterface::class),
            0
        );

        $listener->logCommandTerminate($event);
    }

    public function testLogCommandTerminateNoExecutionId(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleTerminateEvent(
            static::createStub(Command::class),
            static::createStub(InputInterface::class),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects(static::never())
            ->method('fetch')
            ->seal()
        ;

        $listener->logCommandTerminate($event);
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminate(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleTerminateEvent(
            static::createStub(Command::class),
            $this->createOptionExecutionIdInput($listener, $execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects(static::once())
            ->method('fetch')
            ->willReturn($output = uniqid('output-'))
            ->seal()
        ;

        $listener->logCommandTerminate($event);

        self::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_TERMINATED, $execution->getState());
        static::assertSame($output, $execution->getOutput());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminateLongOutput(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleTerminateEvent(
            static::createStub(Command::class),
            $this->createOptionExecutionIdInput($listener, $execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects(static::once())
            ->method('fetch')
            ->willReturn(str_repeat('Z', 50001))
            ->seal()
        ;

        $execution->setOutput('');
        self::$entityManager->flush();

        $listener->logCommandTerminate($event);

        self::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_TERMINATED, $execution->getState());
        static::assertStringContainsString(
            str_repeat('Z', 40000)."\n\n[OUTPUT WAS TOO BIG]\n\nTail of log:\n\n".str_repeat('Z', 10000),
            $execution->getOutput()
        );
    }

    public function testLogCommandErrorNoExecutionId(): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleErrorEvent(
            static::createStub(InputInterface::class),
            static::createStub(BufferedConsoleOutput::class),
            new \Exception()
        );

        $eventDispatcher
            ->expects(static::never())
            ->method('dispatch')
            ->seal()
        ;

        $listener->logCommandError($event);
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandError(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleErrorEvent(
            $this->createOptionExecutionIdInput($listener, $execution->getId()),
            static::createStub(BufferedConsoleOutput::class),
            $error = new \Exception(),
            $command = $this->createMock(Command::class)
        );

        $command
            ->expects(static::once())
            ->method('getApplication')
            ->willReturn($application = $this->createMock(Application::class))
            ->seal()
        ;

        $outputString = uniqid('output-string-');

        $application
            ->expects(static::once())
            ->method('renderThrowable')
            ->with(
                $error,
                static::callback(static function (BufferedOutput $output) use ($outputString) {
                    $output->write($outputString);

                    return true;
                })
            )
            ->seal()
        ;

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (CommandErrorEvent $event) use ($execution, $outputString) {
                    $this->assertSame($execution->getId(), $event->getExecutionId());
                    $this->assertSame($outputString, $event->getOutputString());

                    return true;
                })
            )
            ->willReturnArgument(0)
            ->seal()
        ;

        $listener->logCommandError($event);

        self::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_ERROR, $execution->getState());
        static::assertStringEndsWith($outputString, $execution->getOutput());
        static::assertNull($execution->getAutoAcknowledgeReason());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandErrorAutoAcknowledge(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleErrorEvent(
            $this->createOptionExecutionIdInput($listener, $execution->getId()),
            static::createStub(BufferedConsoleOutput::class),
            new \Exception()
        );

        $reason = uniqid('reason-');
        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(static function (CommandErrorEvent $event) use ($reason) {
                    $event->acknowledge($reason);

                    return true;
                })
            )
            ->willReturnArgument(0)
            ->seal()
        ;

        // If current state is error, state will not be changed
        $execution->setState(Execution::STATE_TERMINATED);
        self::$entityManager->flush();

        $listener->logCommandError($event);

        self::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_AUTO_ACKNOWLEDGE, $execution->getState());
        static::assertSame($reason, $execution->getAutoAcknowledgeReason());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminateDisabled(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleTerminateEvent(
            static::createStub(Command::class),
            $this->createOptionExecutionIdInput($listener, $execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            ConsoleCommandEvent::RETURN_CODE_DISABLED
        );

        $output
            ->expects(static::once())
            ->method('fetch')
            ->willReturn(uniqid('output-'))
            ->seal()
        ;

        $listener->logCommandTerminate($event);

        self::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_DISABLED, $execution->getState());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminateDisabledIgnored(Execution $execution): void
    {
        $listener = new CommandFlowListener(
            self::$entityManager->getConnection(),
            static::createStub(EventDispatcherInterface::class),
            static::createStub(LoggerInterface::class)
        );

        $event = new Event\ConsoleTerminateEvent(
            static::createStub(Command::class),
            $this->createOptionExecutionIdInput($listener, $execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            ConsoleCommandEvent::RETURN_CODE_DISABLED
        );

        $output
            ->expects(static::once())
            ->method('fetch')
            ->willReturn(uniqid('output-'))
            ->seal()
        ;

        ReflectionAccessor::setPropertyValue($listener, 'ignoreDisabledCommand', true);

        $listener->logCommandTerminate($event);

        $execution = self::$entityManager->getRepository(Execution::class)
            ->findOneBy(['id' => $execution->getId()])
        ;

        static::assertNull($execution);
    }

    private function createOptionExecutionIdInput(CommandFlowListener $listener, string $id): InputInterface
    {
        $input = $this->createMock(InputInterface::class);

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with($listener::OPTION_EXECUTION_ID)
            ->willReturn(true)
        ;

        $input
            ->expects(static::once())
            ->method('getOption')
            ->with($listener::OPTION_EXECUTION_ID)
            ->willReturn($id)
            ->seal()
        ;

        return $input;
    }

    private function createCommandEvent(): ConsoleCommandEvent
    {
        $command = new PurgeExecutionCommand(
            self::$entityManager->getConnection(),
            new NullLogger()
        );

        return new ConsoleCommandEvent(
            $command,
            static::createStub(InputInterface::class),
            static::createStub(OutputInterface::class)
        );
    }
}
