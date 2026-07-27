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
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;
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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(CommandFlowListener::class)]
class CommandFlowListenerTest extends TestCase
{
    use DoctrineOrmTrait;
    use MockTrait;

    private static EntityManagerInterface $entityManager;

    private CommandFlowListener $object;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private LoggerInterface&MockObject $logger;

    private ?Execution $execution = null;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = static::setUpMySqlWithAttributeDriver(
            [\dirname((new \ReflectionClass(Execution::class))->getFileName())],
        );
    }

    protected function setUp(): void
    {
        $this->object = new CommandFlowListener(
            self::$entityManager->getConnection(),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $this->logger = $this->createMock(LoggerInterface::class)
        );

        if ($this->execution) {
            self::$entityManager->refresh($this->execution);
        }
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
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
            $this->object::getSubscribedEvents()
        );
    }

    public function testConfigureOptions(): void
    {
        $commandEvent = $this->createCommandEvent();
        $this->object->configureOptions($commandEvent);

        $command = $commandEvent->getCommand();

        $option = $command->getDefinition()->getOption($this->object::OPTION_IGNORE);

        $this->assertSame(
            $this->object::OPTION_IGNORE,
            $option->getName()
        );

        $this->assertNull(
            $option->getShortcut()
        );

        $this->assertFalse(
            $option->isValueRequired()
        );

        $this->assertSame(
            'Flag to ignore login of the execution to the databases.',
            $option->getDescription()
        );

        $this->assertFalse(
            $option->getDefault()
        );

        $option = $command->getDefinition()->getOption($this->object::OPTION_EXECUTION_ID);

        $this->assertSame(
            $this->object::OPTION_EXECUTION_ID,
            $option->getName()
        );

        $this->assertNull(
            $option->getShortcut()
        );

        $this->assertTrue(
            $option->isValueRequired()
        );

        $this->assertNull(
            $option->getDefault()
        );

        $this->assertSame(
            'The existing execution id of the command. Use internally by draw/console.',
            $option->getDescription()
        );
    }

    public function testCheckIgnoredCommandsIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->once())
            ->method('getName')
            ->willReturn('help')
        ;

        $this->object->checkIgnoredCommands($event);

        $this->assertNull($event->getExecutionId());
        $this->assertTrue($event->getIgnoreTracking());
    }

    public function testCheckIgnoredCommandsNotIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->once())
            ->method('getName')
            ->willReturn(uniqid('command-'))
        ;

        $this->object->checkIgnoredCommands($event);

        $this->assertNull($event->getExecutionId());
        $this->assertFalse($event->getIgnoreTracking());
    }

    public function testCheckHelpIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input
            ->expects($this->once())
            ->method('hasOption')
            ->with('help')
            ->willReturn(true)
        ;

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('help')
            ->willReturn(true)
        ;

        $this->object->checkHelp($event);

        $this->assertNull($event->getExecutionId());
        $this->assertTrue($event->getIgnoreTracking());
    }

    public function testCheckHelpNotIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input
            ->expects($this->once())
            ->method('hasOption')
            ->with('help')
            ->willReturn(false)
        ;

        $this->object->checkHelp($event);

        $this->assertNull($event->getExecutionId());
        $this->assertFalse($event->getIgnoreTracking());
    }

    public function testCheckTableExistIgnoredTableDoesNotExists(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $connection = $this->mockProperty(
            $this->object,
            'connection',
            Connection::class
        );

        $connection
            ->expects($this->once())
            ->method('createSchemaManager')
            ->willReturn($schemaManager = $this->createMock(MySQLSchemaManager::class))
        ;

        $schemaManager
            ->expects($this->once())
            ->method('tablesExist')
            ->with(['command__execution'])
            ->willReturn(false)
        ;

        $this->object->checkTableExist($event);

        $this->assertNull($event->getExecutionId());
        $this->assertTrue($event->getIgnoreTracking());
    }

    public function testCheckTableExistIgnoredException(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $connection = $this->mockProperty(
            $this->object,
            'connection',
            Connection::class
        );

        $connection
            ->expects($this->once())
            ->method('createSchemaManager')
            ->willThrowException(new ConnectionException())
        ;

        $this->object->checkTableExist($event);

        $this->assertNull($event->getExecutionId());
        $this->assertTrue($event->getIgnoreTracking());
    }

    public function testLoadIdFromInputNotFound(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->object::OPTION_EXECUTION_ID)
            ->willReturn(false)
        ;

        $this->object->loadIdFromInput($event);

        $this->assertNull($event->getExecutionId());
        $this->assertFalse($event->getIgnoreTracking());
    }

    public function testLoadIdFromInputExists(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($id = uniqid('id-')),
            $this->createMock(OutputInterface::class)
        );

        $this->object->loadIdFromInput($event);

        $this->assertSame($id, $event->getExecutionId());
        $this->assertFalse($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabaseIgnoredException(): void
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->once())
            ->method('getName')
            ->willReturn(uniqid('command-'))
        ;

        $input
            ->expects($this->once())
            ->method('getArguments')
            ->willReturn([])
        ;

        $input
            ->expects($this->once())
            ->method('getOptions')
            ->willReturn([])
        ;

        $connection = $this->mockProperty(
            $this->object,
            'connection',
            PrimaryReadReplicaConnection::class
        );

        $connection
            ->expects($this->once())
            ->method('isConnectedToPrimary')
            ->willReturn(false)
        ;

        $connection
            ->expects($this->once())
            ->method('insert')
            ->willThrowException($error = new \Exception())
        ;

        $connection
            ->expects($this->once())
            ->method('ensureConnectedToReplica')
        ;

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Command flow listener error while generating execution id',
                ['error' => $error]
            )
        ;

        $this->object->generateFromDatabase($event);

        $this->assertNull($event->getExecutionId());
        $this->assertTrue($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabase(): void
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->once())
            ->method('getName')
            ->willReturn($commandName = uniqid('command-'))
        ;

        $input
            ->expects($this->once())
            ->method('getArguments')
            ->willReturn(['keyName' => 'keyValue'])
        ;

        $input
            ->expects($this->once())
            ->method('getOptions')
            ->willReturn(['null' => null, 'zero' => 0, 'false' => false, 'other' => 'value'])
        ;

        $connection = $this->mockProperty(
            $this->object,
            'connection',
            PrimaryReadReplicaConnection::class
        );

        $connection
            ->expects($this->once())
            ->method('isConnectedToPrimary')
            ->willReturn(false)
        ;

        $connection
            ->expects($this->once())
            ->method('insert')
            ->with(
                'command__execution',
                $this->callback(function (array $arguments) use ($commandName) {
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
            ->expects($this->once())
            ->method('ensureConnectedToReplica')
        ;

        $this->object->generateFromDatabase($event);

        $this->assertIsString($event->getExecutionId());
        $this->assertFalse($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabaseReal(): Execution
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->once())
            ->method('getName')
            ->willReturn(uniqid('command-'))
        ;

        $input
            ->expects($this->once())
            ->method('getArguments')
            ->willReturn([])
        ;

        $input
            ->expects($this->once())
            ->method('getOptions')
            ->willReturn([])
        ;

        $this->object->generateFromDatabase($event);

        $this->assertNotNull($id = $event->getExecutionId());
        $this->assertFalse($event->getIgnoreTracking());

        $this->execution = self::$entityManager->find(Execution::class, $id);

        return $this->execution;
    }

    public function testLogCommandStartNoExecutionId(): void
    {
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnArgument(0)
        ;

        $event = new ConsoleCommandEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->never())
            ->method('getDefinition')
        ;

        $this->object->logCommandStart($event);
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandStart(Execution $execution): void
    {
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(static function (LoadExecutionIdEvent $event) use ($execution) {
                    $event->setExecutionId($execution->getId());

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        $event = new ConsoleCommandEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definition = new InputDefinition())
        ;

        $definition->addOption(
            $option = new InputOption($this->object::OPTION_EXECUTION_ID, null, InputOption::VALUE_REQUIRED)
        );

        $execution->setState(uniqid('state-'));
        self::$entityManager->flush();

        $this->object->logCommandStart($event);

        $this->assertSame($execution->getId(), $option->getDefault());

        self::$entityManager->refresh($execution);

        $this->assertSame(Execution::STATE_STARTED, $execution->getState());
    }

    public function testLogCommandTerminateReplication(): void
    {
        $connection = $this->mockProperty(
            $this->object,
            'connection',
            PrimaryReadReplicaConnection::class
        );

        $connection
            ->expects($this->once())
            ->method('isConnectedToPrimary')
            ->willReturn(false)
        ;

        $connection
            ->expects($this->once())
            ->method('ensureConnectedToReplica')
        ;

        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput(uniqid('id-')),
            $this->createMock(OutputInterface::class),
            0
        );

        $this->object->logCommandTerminate($event);
    }

    public function testLogCommandTerminateNoExecutionId(): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects($this->never())
            ->method('fetch')
        ;

        $this->object->logCommandTerminate($event);
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminate(Execution $execution): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($output = uniqid('output-'))
        ;

        $this->object->logCommandTerminate($event);

        self::$entityManager->refresh($execution);

        $this->assertSame(Execution::STATE_TERMINATED, $execution->getState());
        $this->assertSame($output, $execution->getOutput());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminateLongOutput(Execution $execution): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(str_repeat('Z', 50001))
        ;

        $execution->setOutput('');
        self::$entityManager->flush();

        $this->object->logCommandTerminate($event);

        self::$entityManager->refresh($execution);

        $this->assertSame(Execution::STATE_TERMINATED, $execution->getState());
        $this->assertStringContainsString(
            str_repeat('Z', 40000)."\n\n[OUTPUT WAS TOO BIG]\n\nTail of log:\n\n".str_repeat('Z', 10000),
            $execution->getOutput()
        );
    }

    public function testLogCommandErrorNoExecutionId(): void
    {
        $event = new Event\ConsoleErrorEvent(
            $this->createMock(InputInterface::class),
            $this->createMock(BufferedConsoleOutput::class),
            new \Exception()
        );

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch')
        ;

        $this->object->logCommandError($event);
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandError(Execution $execution): void
    {
        $event = new Event\ConsoleErrorEvent(
            $this->createOptionExecutionIdInput($execution->getId()),
            $this->createMock(BufferedConsoleOutput::class),
            $error = new \Exception(),
            $command = $this->createMock(Command::class)
        );

        $command
            ->expects($this->once())
            ->method('getApplication')
            ->willReturn($application = $this->createMock(Application::class))
        ;

        $outputString = uniqid('output-string-');

        $application
            ->expects($this->once())
            ->method('renderThrowable')
            ->with(
                $error,
                $this->callback(static function (BufferedOutput $output) use ($outputString) {
                    $output->write($outputString);

                    return true;
                })
            )
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (CommandErrorEvent $event) use ($execution, $outputString) {
                    $this->assertSame($execution->getId(), $event->getExecutionId());
                    $this->assertSame($outputString, $event->getOutputString());

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        $this->object->logCommandError($event);

        self::$entityManager->refresh($execution);

        $this->assertSame(Execution::STATE_ERROR, $execution->getState());
        $this->assertStringEndsWith($outputString, $execution->getOutput());
        $this->assertNull($execution->getAutoAcknowledgeReason());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandErrorAutoAcknowledge(Execution $execution): void
    {
        $event = new Event\ConsoleErrorEvent(
            $this->createOptionExecutionIdInput($execution->getId()),
            $this->createMock(BufferedConsoleOutput::class),
            new \Exception()
        );

        $reason = uniqid('reason-');
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(static function (CommandErrorEvent $event) use ($reason) {
                    $event->acknowledge($reason);

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        // If current state is error, state will not be changed
        $execution->setState(Execution::STATE_TERMINATED);
        self::$entityManager->flush();

        $this->object->logCommandError($event);

        self::$entityManager->refresh($execution);

        $this->assertSame(Execution::STATE_AUTO_ACKNOWLEDGE, $execution->getState());
        $this->assertSame($reason, $execution->getAutoAcknowledgeReason());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminateDisabled(Execution $execution): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            ConsoleCommandEvent::RETURN_CODE_DISABLED
        );

        $output
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(uniqid('output-'))
        ;

        $this->object->logCommandTerminate($event);

        self::$entityManager->refresh($execution);

        $this->assertSame(Execution::STATE_DISABLED, $execution->getState());
    }

    #[Depends('testGenerateFromDatabaseReal')]
    public function testLogCommandTerminateDisabledIgnored(Execution $execution): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            ConsoleCommandEvent::RETURN_CODE_DISABLED
        );

        $output
            ->expects($this->once())
            ->method('fetch')
            ->willReturn(uniqid('output-'))
        ;

        ReflectionAccessor::setPropertyValue($this->object, 'ignoreDisabledCommand', true);

        $this->object->logCommandTerminate($event);

        $execution = self::$entityManager->getRepository(Execution::class)
            ->findOneBy(['id' => $execution->getId()])
        ;

        $this->assertNull($execution);
    }

    private function createOptionExecutionIdInput(string $id): InputInterface
    {
        $input = $this->createMock(InputInterface::class);

        $input
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->object::OPTION_EXECUTION_ID)
            ->willReturn(true)
        ;

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with($this->object::OPTION_EXECUTION_ID)
            ->willReturn($id)
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
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );
    }
}
