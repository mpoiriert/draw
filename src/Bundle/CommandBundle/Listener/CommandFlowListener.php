<?php

namespace Draw\Bundle\CommandBundle\Listener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Doctrine\DBAL\DBALException;
use Draw\Bundle\CommandBundle\Entity\Execution;
use ErrorException;
use Exception;
use Symfony\Component\Console\Event;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandFlowListener implements EventSubscriberInterface
{
    const OPTION_EXECUTION_ID = 'draw-execution-id';

    const OPTION_IGNORE = 'draw-execution-ignore';

    private $commandsToIgnore = [
        'help',
        'doctrine:database:drop',
        'doctrine:database:create',
        'cache:clear',
    ];

    /**
     * @var Connection|MasterSlaveConnection
     */
    private $connection;

    public static function getSubscribedEvents()
    {
        return [
            Event\ConsoleCommandEvent::class => [
                ['addOptions', 255],
                ['setIgnoreFlag', -1],
                ['logCommandStart', 0],
            ],
            Event\ConsoleTerminateEvent::class => ['logCommandTerminate'],
            Event\ConsoleErrorEvent::class => ['logCommandError'],
        ];
    }

    public function __construct(Connection $executionConnection)
    {
        $this->connection = $executionConnection;
    }

    public function addOptions(Event\ConsoleCommandEvent $consoleCommandEvent): void
    {
        $consoleCommandEvent
            ->getCommand()
            ->addOption(
                CommandFlowListener::OPTION_IGNORE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Flag to ignore login of the execution to the databases.'
            );
    }

    public function setIgnoreFlag(Event\ConsoleCommandEvent $consoleCommandEvent): void
    {
        $option = $consoleCommandEvent->getCommand()
            ->getDefinition()
            ->getOption(CommandFlowListener::OPTION_IGNORE);

        $commandName = $consoleCommandEvent->getCommand()->getName();

        if (in_array($commandName, $this->commandsToIgnore)) {
            $option->setDefault(true);

            return;
        }

        if (!$consoleCommandEvent->commandShouldRun()) {
            $option->setDefault(true);

            return;
        }

        if ($consoleCommandEvent->getInput()->getOption('help')) {
            $option->setDefault(true);

            return;
        }

        try {
            if (!$this->connection->getSchemaManager()->tablesExist(['command__execution'])) {
                $option->setDefault(true);

                return;
            }
        } catch (DBALException $exception) {
            $option->setDefault(true);

            return;
        }
    }

    public function logCommandStart(Event\ConsoleCommandEvent $consoleCommandEvent): void
    {
        $consoleCommandEvent->getInput()->bind($consoleCommandEvent->getCommand()->getDefinition());

        if ($consoleCommandEvent->getInput()->getOption(CommandFlowListener::OPTION_IGNORE)) {
            return;
        }

        $executionId = $this->generateExecutionId($consoleCommandEvent);

        $consoleCommandEvent
            ->getCommand()
            ->addOption(
                CommandFlowListener::OPTION_EXECUTION_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'The existing execution id of the command. Use internally by the DrawCommandBundle.',
                $executionId
            );
    }

    public function logCommandTerminate(Event\ConsoleTerminateEvent $consoleCommandEvent)
    {
        if ($executionId = $this->getExecutionId($consoleCommandEvent)) {
            $output = $consoleCommandEvent->getOutput();
            $outputString = null;
            if (method_exists($output, 'fetch')) {
                $outputString = $output->fetch();
            }

            $this->updateState($executionId, Execution::STATE_TERMINATED, $outputString);
        }
    }

    public function logCommandError(Event\ConsoleErrorEvent $exceptionEvent): void
    {
        if ($executionId = $this->getExecutionId($exceptionEvent)) {
            $e = $exceptionEvent->getError();
            if (!$e instanceof Exception) {
                $e = class_exists(FatalThrowableError::class)
                    ? new FatalThrowableError($e)
                    : new ErrorException($e->getMessage(), $e->getCode(), E_ERROR, $e->getFile(), $e->getLine());
            }

            $output = new BufferedOutput(Output::VERBOSITY_DEBUG, true);
            $exceptionEvent->getCommand()->getApplication()->renderException($e, $output);
            $this->updateState($executionId, Execution::STATE_ERROR, $output->fetch());
        }
    }

    private function getExecutionId(Event\ConsoleEvent $event): ?string
    {
        $input = $event->getInput();
        if (!$input->hasOption(CommandFlowListener::OPTION_EXECUTION_ID)) {
            return null;
        }

        return $input->getOption(CommandFlowListener::OPTION_EXECUTION_ID);
    }

    private function updateState(string $executionId, string $state, string $outputString = null): void
    {
        $reconnectToSlave = $this->mustReconnectToSlave();

        $date = date('Y-m-d H:i:s');
        $parameters = [
            'id' => $executionId,
            'updated_at' => $date,
            'state' => $state,
        ];

        $setOutput = null;
        if ($outputString) {
            $setOutput = 'output = CONCAT(:output, output),';
            $parameters['output'] = $outputString;
        }

        $query = <<<SQL
UPDATE 
  command__execution 
SET 
  updated_at = :updated_at,
  $setOutput
  state = IF(state != 'error', :state, state)
WHERE
id = :id
SQL;

        $this->connection->prepare($query)->execute($parameters);

        if ($reconnectToSlave) {
            $this->connection->connect('slave');
        }
    }

    private function mustReconnectToSlave(): bool
    {
        return $this->connection instanceof MasterSlaveConnection && !$this->connection->isConnectedToMaster();
    }

    private function generateExecutionId(Event\ConsoleCommandEvent $consoleCommandEvent): string
    {
        $reconnectToSlave = $this->mustReconnectToSlave();

        $input = $consoleCommandEvent->getInput();
        $parameters = $input->getArguments();

        $options = array_filter($input->getOptions(), function ($value) {
            // We want to keep 0 value
            return false !== $value && null !== $value;
        });

        foreach ($options as $key => $value) {
            $parameters['--'.$key] = $value;
        }

        $date = date('Y-m-d H:i:s');
        $query = <<<SQL
INSERT INTO 
  command__execution (command_name, created_at, updated_at, state, input, output) 
  VALUES (:name, :created_at, :updated_at, :state, :input, :output)
SQL;
        $this->connection
            ->prepare($query)
            ->execute(
                [
                    'name' => $consoleCommandEvent->getCommand()->getName(),
                    'created_at' => $date,
                    'updated_at' => $date,
                    'output' => '',
                    'state' => Execution::STATE_STARTED,
                    'input' => json_encode($parameters),
                ]
            );

        $executionId = $this->connection->lastInsertId();

        if ($reconnectToSlave) {
            $this->connection->connect('slave');
        }

        return $executionId;
    }
}
