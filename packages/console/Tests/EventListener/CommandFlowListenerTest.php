<?php

namespace Draw\Component\Console\Tests\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Exception as DBALException;
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
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommandFlowListenerTest extends TestCase
{
    use DoctrineOrmTrait;

    private static EntityManagerInterface $entityManager;

    private CommandFlowListener $object;

    private EventDispatcherInterface $eventDispatcher;

    private ?Execution $execution = null;

    public static function setUpBeforeClass(): void
    {
        static::$entityManager = static::setUpMySqlWithAnnotationDriver(
            [\dirname((new \ReflectionClass(Execution::class))->getFileName())],
        );
    }

    protected function setUp(): void
    {
        $this->object = new CommandFlowListener(
            static::$entityManager->getConnection(),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        if ($this->execution) {
            static::$entityManager->refresh($this->execution);
        }
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                LoadExecutionIdEvent::class => [
                    ['checkIgnoredCommands'],
                    ['checkHelp'],
                    ['checkTableExist'],
                    ['loadIdFromInput', -10],
                    ['generateFromDatabase', -10],
                ],
                Event\ConsoleCommandEvent::class => [
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

        static::assertSame(
            $this->object::OPTION_IGNORE,
            $option->getName()
        );

        static::assertNull(
            $option->getShortcut()
        );

        static::assertTrue(
            $option->isValueOptional()
        );

        static::assertSame(
            'Flag to ignore login of the execution to the databases.',
            $option->getDescription()
        );

        static::assertNull(
            $option->getDefault()
        );

        $option = $command->getDefinition()->getOption($this->object::OPTION_EXECUTION_ID);

        static::assertSame(
            $this->object::OPTION_EXECUTION_ID,
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
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn('help');

        $this->object->checkIgnoredCommands($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testCheckIgnoredCommandsNotIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn(uniqid('command-'));

        $this->object->checkIgnoredCommands($event);

        static::assertNull($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testCheckHelpIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with('help')
            ->willReturn(true);

        $input
            ->expects(static::once())
            ->method('getOption')
            ->with('help')
            ->willReturn(true);

        $this->object->checkHelp($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testCheckHelpNotIgnored(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with('help')
            ->willReturn(false);

        $this->object->checkHelp($event);

        static::assertNull($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testCheckTableExistIgnoredTableDoesNotExists(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        ReflectionAccessor::setPropertyValue(
            $this->object,
            'connection',
            $connection = $this->createMock(Connection::class),
        );

        $connection
            ->expects(static::once())
            ->method('createSchemaManager')
            ->willReturn($schemaManager = $this->createMock(MySQLSchemaManager::class));

        $schemaManager
            ->expects(static::once())
            ->method('tablesExist')
            ->with(['command__execution'])
            ->willReturn(false);

        $this->object->checkTableExist($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testCheckTableExistIgnoredException(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        ReflectionAccessor::setPropertyValue(
            $this->object,
            'connection',
            $connection = $this->createMock(Connection::class),
        );

        $connection
            ->expects(static::once())
            ->method('createSchemaManager')
            ->willThrowException(new DBALException());

        $this->object->checkTableExist($event);

        static::assertNull($event->getExecutionId());
        static::assertTrue($event->getIgnoreTracking());
    }

    public function testLoadIdFromInputNotFound(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with($this->object::OPTION_EXECUTION_ID)
            ->willReturn(false);

        $this->object->loadIdFromInput($event);

        static::assertNull($event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testLoadIdFromInputExists(): void
    {
        $event = new LoadExecutionIdEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($id = rand(1, \PHP_INT_MAX)),
            $this->createMock(OutputInterface::class)
        );

        $this->object->loadIdFromInput($event);

        static::assertSame($id, $event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabase(): void
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn($commandName = uniqid('command-'));

        $input
            ->expects(static::once())
            ->method('getArguments')
            ->willReturn(['keyName' => 'keyValue']);

        $input
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn(['null' => null, 'zero' => 0, 'false' => false, 'other' => 'value']);

        ReflectionAccessor::setPropertyValue(
            $this->object,
            'connection',
            $connection = $this->createMock(PrimaryReadReplicaConnection::class),
        );

        $connection
            ->expects(static::once())
            ->method('isConnectedToPrimary')
            ->willReturn(false);

        $connection
            ->expects(static::once())
            ->method('insert')
            ->with(
                'command__execution',
                static::callback(function (array $arguments) use ($commandName) {
                    $this->assertCount(6, $arguments);

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
            );

        $connection
            ->expects(static::once())
            ->method('lastInsertId')
            ->willReturn($id = rand(1, \PHP_INT_MAX));

        $connection
            ->expects(static::once())
            ->method('ensureConnectedToReplica');

        $this->object->generateFromDatabase($event);

        static::assertSame($id, $event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());
    }

    public function testGenerateFromDatabaseReal(): Execution
    {
        $event = new LoadExecutionIdEvent(
            $command = $this->createMock(Command::class),
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getName')
            ->willReturn(uniqid('command-'));

        $input
            ->expects(static::once())
            ->method('getArguments')
            ->willReturn([]);

        $input
            ->expects(static::once())
            ->method('getOptions')
            ->willReturn([]);

        $this->object->generateFromDatabase($event);

        static::assertNotNull($id = $event->getExecutionId());
        static::assertFalse($event->getIgnoreTracking());

        $this->execution = static::$entityManager->find(Execution::class, $id);

        return $this->execution;
    }

    public function testLogCommandStartNoExecutionId(): void
    {
        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->willReturnArgument(0);

        $event = new Event\ConsoleCommandEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects(static::never())
            ->method('getDefinition');

        $this->object->logCommandStart($event);
    }

    /**
     * @depends testGenerateFromDatabaseReal
     */
    public function testLogCommandStart(Execution $execution): void
    {
        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (LoadExecutionIdEvent $event) use ($execution) {
                    $event->setExecutionId($execution->getId());

                    return true;
                })
            )
            ->willReturnArgument(0);

        $event = new Event\ConsoleCommandEvent(
            $command = $this->createMock(Command::class),
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $command
            ->expects(static::once())
            ->method('getDefinition')
            ->willReturn($definition = new InputDefinition());

        $definition->addOption(
            $option = new InputOption($this->object::OPTION_EXECUTION_ID, null, InputOption::VALUE_REQUIRED)
        );

        $execution->setState(uniqid('state-'));
        static::$entityManager->flush();

        $this->object->logCommandStart($event);

        static::assertSame($execution->getId(), $option->getDefault());

        static::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_STARTED, $execution->getState());
    }

    public function testLogCommandTerminateReplication(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->object,
            'connection',
            $connection = $this->createMock(PrimaryReadReplicaConnection::class),
        );

        $connection
            ->expects(static::once())
            ->method('isConnectedToPrimary')
            ->willReturn(false);

        $connection
            ->expects(static::once())
            ->method('ensureConnectedToReplica');

        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput(-1),
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
            ->expects(static::never())
            ->method('fetch');

        $this->object->logCommandTerminate($event);
    }

    /**
     * @depends testGenerateFromDatabaseReal
     */
    public function testLogCommandTerminate(Execution $execution): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects(static::once())
            ->method('fetch')
            ->willReturn($output = uniqid('output-'));

        $this->object->logCommandTerminate($event);

        static::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_TERMINATED, $execution->getState());
        static::assertSame($output, $execution->getOutput());
    }

    /**
     * @depends testGenerateFromDatabaseReal
     */
    public function testLogCommandTerminateLongOutput(Execution $execution): void
    {
        $event = new Event\ConsoleTerminateEvent(
            $this->createMock(Command::class),
            $this->createOptionExecutionIdInput($execution->getId()),
            $output = $this->createMock(BufferedConsoleOutput::class),
            0
        );

        $output
            ->expects(static::once())
            ->method('fetch')
            ->willReturn(str_repeat('Z', 50001));

        $execution->setOutput('');
        static::$entityManager->flush();

        $this->object->logCommandTerminate($event);

        static::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_TERMINATED, $execution->getState());
        static::assertStringContainsString(
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
            ->expects(static::never())
            ->method('dispatch');

        $this->object->logCommandError($event);
    }

    /**
     * @depends testGenerateFromDatabaseReal
     */
    public function testLogCommandError(Execution $execution): void
    {
        $event = new Event\ConsoleErrorEvent(
            $this->createOptionExecutionIdInput($execution->getId()),
            $this->createMock(BufferedConsoleOutput::class),
            $error = new \Exception(),
            $command = $this->createMock(Command::class)
        );

        $command
            ->expects(static::once())
            ->method('getApplication')
            ->willReturn($application = $this->createMock(Application::class));

        $outputString = uniqid('output-string-');

        $application
            ->expects(static::once())
            ->method('renderThrowable')
            ->with(
                $error,
                static::callback(function (BufferedOutput $output) use ($outputString) {
                    $output->write($outputString);

                    return true;
                })
            );

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (CommandErrorEvent $event) use ($execution, $outputString) {
                    $this->assertSame($execution->getId(), $event->getExecutionId());
                    $this->assertSame($outputString, $event->getOutputString());

                    return true;
                })
            )
            ->willReturnArgument(0);

        $this->object->logCommandError($event);

        static::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_ERROR, $execution->getState());
        static::assertStringEndsWith($outputString, $execution->getOutput());
        static::assertNull($execution->getAutoAcknowledgeReason());
    }

    /**
     * @depends testGenerateFromDatabaseReal
     */
    public function testLogCommandErrorAutoAcknowledge(Execution $execution): void
    {
        $event = new Event\ConsoleErrorEvent(
            $this->createOptionExecutionIdInput($execution->getId()),
            $this->createMock(BufferedConsoleOutput::class),
            new \Exception()
        );

        $reason = uniqid('reason-');
        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (CommandErrorEvent $event) use ($reason) {
                    $event->acknowledge($reason);

                    return true;
                })
            )
            ->willReturnArgument(0);

        // If current state is error, state will not be changed
        $execution->setState(Execution::STATE_TERMINATED);
        static::$entityManager->flush();

        $this->object->logCommandError($event);

        static::$entityManager->refresh($execution);

        static::assertSame(Execution::STATE_AUTO_ACKNOWLEDGE, $execution->getState());
        static::assertSame($reason, $execution->getAutoAcknowledgeReason());
    }

    private function createOptionExecutionIdInput(int $id): InputInterface
    {
        $input = $this->createMock(InputInterface::class);

        $input
            ->expects(static::once())
            ->method('hasOption')
            ->with($this->object::OPTION_EXECUTION_ID)
            ->willReturn(true);

        $input
            ->expects(static::once())
            ->method('getOption')
            ->with($this->object::OPTION_EXECUTION_ID)
            ->willReturn($id);

        return $input;
    }

    private function createCommandEvent(): Event\ConsoleCommandEvent
    {
        $command = new PurgeExecutionCommand(
            static::$entityManager->getConnection(),
            new NullLogger()
        );

        return new Event\ConsoleCommandEvent(
            $command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );
    }
}
