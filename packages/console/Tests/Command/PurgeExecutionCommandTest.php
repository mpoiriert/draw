<?php

namespace Draw\Component\Console\Tests\Command;

use Doctrine\DBAL\Connection;
use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

#[CoversClass(PurgeExecutionCommand::class)]
class PurgeExecutionCommandTest extends TestCase
{
    use CommandTestTrait;
    use MockTrait;

    private Connection&MockObject $connection;

    private LoggerInterface&MockObject $logger;

    public function createCommand(): Command
    {
        $this->connection = $this->createMock(Connection::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        return new PurgeExecutionCommand($this->connection, $this->logger);
    }

    public function getCommandName(): string
    {
        return 'draw:console:purge-execution';
    }

    public static function provideTestArgument(): iterable
    {
        return [];
    }

    public static function provideTestOption(): iterable
    {
        yield [
            'delay',
            null,
            InputOption::VALUE_OPTIONAL,
            '-1 month',
        ];

        yield [
            'batch-size',
            null,
            InputOption::VALUE_OPTIONAL,
            1000,
        ];

        yield [
            'sleep',
            null,
            InputOption::VALUE_OPTIONAL,
            10,
        ];
    }

    public function testExecuteInvalidBatchSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Batch size must be a integer >= 1');

        $this->execute(['--batch-size' => -1]);
    }

    public function testExecuteInvalidSleep(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sleep must be integer >= 0');

        $this->execute(['--sleep' => -1]);
    }

    public function testExecute(): void
    {
        $date = '2000-01-01 00:00:01';

        $this->logger->expects(
            static::exactly(3)
        )
            ->method('debug')
            ->with(
                ...static::withConsecutive(
                    [
                        'Purging all records before {delay}, {batch_size} at the time, sleeping {seconds} per batch.',
                        ['delay' => $date, 'batch_size' => 1000, 'seconds' => 0],
                    ],
                    [
                        'Sleeping for {seconds} seconds during purge.',
                        ['seconds' => 0],
                    ],
                    [
                        'Successfully purged {record_count} records.',
                        ['record_count' => 1002],
                    ]
                )
            );

        $this->connection->expects(static::exactly(2))
            ->method('executeStatement')
            ->with(
                ...static::withConsecutive(
                    [
                        'DELETE FROM command__execution WHERE state = ? AND updated_at < ? LIMIT ?',
                        ['terminated', new \DateTime($date), 1000],
                        ['string', 'datetime', 'integer'],
                    ],
                    [
                        'DELETE FROM command__execution WHERE state = ? AND updated_at < ? LIMIT ?',
                        ['terminated', new \DateTime($date), 1000],
                        ['string', 'datetime', 'integer'],
                    ]
                )
            )
            ->willReturnOnConsecutiveCalls(1000, 2);

        $this->execute(['--delay' => $date, '--sleep' => 0])
            ->test(CommandDataTester::create());
    }
}
