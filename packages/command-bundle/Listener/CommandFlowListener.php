<?php

namespace Draw\Bundle\CommandBundle\Listener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Exception as DBALException;
use Draw\Bundle\CommandBundle\Entity\Execution;
use Draw\Bundle\CommandBundle\Event\CommandErrorEvent;
use Symfony\Component\Console\Event;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandFlowListener implements EventSubscriberInterface
{
    public const OPTION_EXECUTION_ID = 'draw-execution-id';

    public const OPTION_IGNORE = 'draw-execution-ignore';

    private $commandsToIgnore = [
        'help',
        'doctrine:database:drop',
        'doctrine:database:create',
        'cache:clear',
    ];

    /**
     * @var Connection|PrimaryReadReplicaConnection
     */
    private $connection;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public static function getSubscribedEvents(): array
    {
        return [
            Event\ConsoleCommandEvent::class => [
                ['setIgnoreFlag', 1],
                ['logCommandStart', 0],
            ],
            Event\ConsoleTerminateEvent::class => ['logCommandTerminate'],
            Event\ConsoleErrorEvent::class => ['logCommandError'],
        ];
    }

    public function __construct(
        Connection $executionConnection,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->connection = $executionConnection;
        $this->eventDispatcher = $eventDispatcher;
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
        if ($consoleCommandEvent->getInput()->getOption(self::OPTION_IGNORE)) {
            return;
        }

        $input = $consoleCommandEvent->getInput();
        if ($executionId = $input->getOption(self::OPTION_EXECUTION_ID)) {
            $this->updateState($executionId, Execution::STATE_STARTED);
        } else {
            $executionId = $this->generateExecutionId($consoleCommandEvent);
        }

        $option = $consoleCommandEvent->getCommand()->getDefinition()->getOption(self::OPTION_EXECUTION_ID);
        $option->setDefault($executionId);
    }

    public function logCommandTerminate(Event\ConsoleTerminateEvent $consoleCommandEvent): void
    {
        if (null === $executionId = $this->getExecutionId($consoleCommandEvent)) {
            return;
        }

        $output = $consoleCommandEvent->getOutput();
        $outputString = null;
        if (method_exists($output, 'fetch')) {
            $outputString = $output->fetch();
        }

        $this->updateState($executionId, Execution::STATE_TERMINATED, $outputString);
    }

    public function logCommandError(Event\ConsoleErrorEvent $exceptionEvent): void
    {
        if (null === $executionId = $this->getExecutionId($exceptionEvent)) {
            return;
        }

        $error = $exceptionEvent->getError();

        $output = new BufferedOutput(Output::VERBOSITY_DEBUG, true);
        $exceptionEvent->getCommand()->getApplication()->renderThrowable($error, $output);
        $outputString = $output->fetch();

        $commandErrorEvent = new CommandErrorEvent($executionId, $outputString);
        $this->eventDispatcher->dispatch($commandErrorEvent);

        $executionState = $commandErrorEvent->isAutoAcknowledge() ? Execution::STATE_AUTO_ACKNOWLEDGE : Execution::STATE_ERROR;
        $this->updateState($executionId, $executionState, $outputString,
            $commandErrorEvent->getAutoAcknowledgeReason());
    }

    private function getExecutionId(Event\ConsoleEvent $event): ?string
    {
        $input = $event->getInput();
        if (!$input->hasOption(CommandFlowListener::OPTION_EXECUTION_ID)) {
            return null;
        }

        return $input->getOption(CommandFlowListener::OPTION_EXECUTION_ID);
    }

    private function updateState(
        string $executionId,
        string $state,
        ?string $outputString = null,
        ?string $autoAcknowledgeReason = null
    ): void {
        if (mb_strlen((string) $outputString) > 50000) {
            $outputString = mb_substr($outputString, 0,
                    40000)."\n\n[OUTPUT WAS TOO BIG]\n\nTail of log:\n\n".mb_substr(
                    $outputString,
                    -10000
                );
        }

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

        $setAutoAcknowledgeReason = null;
        if ($autoAcknowledgeReason) {
            $parameters['auto_acknowledge_reason'] = $autoAcknowledgeReason;
            $setAutoAcknowledgeReason = 'auto_acknowledge_reason = :auto_acknowledge_reason,';
        }

        $query = <<<SQL
UPDATE 
  command__execution 
SET 
  updated_at = :updated_at,
  $setOutput
  $setAutoAcknowledgeReason
  state = IF(state != 'error', :state, state)
WHERE
id = :id
SQL;

        $this->connection->prepare($query)->execute($parameters);

        if ($reconnectToSlave) {
            $this->connection->ensureConnectedToReplica();
        }
    }

    private function mustReconnectToSlave(): bool
    {
        return $this->connection instanceof PrimaryReadReplicaConnection && !$this->connection->isConnectedToPrimary();
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
            $this->connection->ensureConnectedToReplica();
        }

        return (int) $executionId;
    }
}
