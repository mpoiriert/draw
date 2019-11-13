<?php namespace Draw\Bundle\CommandBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Draw\Bundle\CommandBundle\Entity\Execution;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeExecutionCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const DEFAULT_DELAY = '-1 month';
    public const DEFAULT_WAIT_SECOND = 10;
    public const DEFAULT_BATCH_SIZE = 1000;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $executionConnection)
    {
        parent::__construct();
        $this->connection = $executionConnection;
        $this->logger = new NullLogger();
    }

    protected function configure()
    {
        $this
            ->setName('draw:command:purge-execution')
            ->setDescription('Purge the execution table of all records before a specified date interval.')
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Records older than this date interval will be deleted.',
                self::DEFAULT_DELAY
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Delete this many rows as a batch in a loop.',
                self::DEFAULT_BATCH_SIZE
            )
            ->addOption(
                'sleep',
                null,
                InputOption::VALUE_OPTIONAL,
                'The delete loop will sleep this long in seconds between iteration.',
                self::DEFAULT_WAIT_SECOND
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $delay = new \DateTime($input->getOption('delay'));
        $batchSize = (int)$input->getOption('batch-size');
        $seconds = (int)$input->getOption('sleep');

        if ($batchSize < 1) {
            throw new \InvalidArgumentException('Batch size must be a integer >= 1');
        }

        if ($seconds < 0) {
            throw new \InvalidArgumentException('Sleep must be integer >= 0');
        }

        $this->logger->debug(
            'Purging all records before {delay}, {batch_size} as the time, sleeping {seconds} per batch.',
            ['delay' => $delay->format('Y-m-d H:i:s'), 'batch_size' => $batchSize, 'seconds' => $seconds]
        );

        $recordCount = $this->purge($delay, $batchSize, $seconds);

        $this->logger->debug(
            'Successfully purged {record_count} records.',
            ['record_count' => $recordCount]
        );
    }

    /**
     * @param \DateTime $before
     * @param int $batchSize
     * @param int $seconds
     * @return int The total number of records purged
     */
    private function purge(
        \DateTime $before,
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        $seconds = self::DEFAULT_WAIT_SECOND
    ): int {
        $total = 0;
        while ($affectedRows = $this->purgeBatch($before, $batchSize)) {
            $total += $affectedRows;

            if ($affectedRows < $batchSize) {
                break;
            }

            $this->logger->debug('Sleeping for {seconds} seconds during purge.', ['seconds' => $seconds]);
            usleep($seconds * 1000000);
        }

        return $total;
    }

    /**
     * @param \DateTime $before
     * @param int $batchSize
     * @return int The number of affected rows
     */
    private function purgeBatch(\DateTime $before, int $batchSize): int
    {
        return $this->connection->executeUpdate(
            'DELETE FROM command__execution WHERE state = ? AND updated_at < ? LIMIT ?',
            [Execution::STATE_TERMINATED, $before, $batchSize],
            [Type::STRING, Type::DATETIME, Type::INTEGER]
        );
    }
}
