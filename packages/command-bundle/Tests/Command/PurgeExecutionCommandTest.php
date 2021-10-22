<?php

namespace Draw\Bundle\CommandBundle\Tests\Command;

use Doctrine\DBAL\Connection;
use Draw\Bundle\CommandBundle\Command\PurgeExecutionCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class PurgeExecutionCommandTest extends CommandTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $connectionProphecy;

    /**
     * @var ObjectProphecy
     */
    private $loggerProphecy;

    public function createCommand(): Command
    {
        $this->connectionProphecy = $this->prophesize(Connection::class);
        $command = new PurgeExecutionCommand($this->connectionProphecy->reveal(Connection::class));

        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
        $command->setLogger($this->loggerProphecy->reveal(LoggerInterface::class));

        return $command;
    }

    public function getCommandName(): string
    {
        return 'draw:command:purge-execution';
    }

    public function getCommandDescription(): string
    {
        return 'Purge the execution table of all records before a specified date interval.';
    }

    public function provideTestArgument(): iterable
    {
        return [];
    }

    public function provideTestOption(): iterable
    {
        yield [
            'delay',
            null,
            InputOption::VALUE_OPTIONAL,
            'Records older than this date interval will be deleted.',
            '-1 month',
        ];

        yield [
            'batch-size',
            null,
            InputOption::VALUE_OPTIONAL,
            'Delete this many rows as a batch in a loop.',
            1000,
        ];

        yield [
            'sleep',
            null,
            InputOption::VALUE_OPTIONAL,
            'The delete loop will sleep this long in seconds between iteration.',
            10,
        ];
    }

    public function testExecute()
    {
        $date = '2000-01-01 00:00:01';

        $this->loggerProphecy
            ->__call(
                'debug',
                [
                    'Purging all records before {delay}, {batch_size} as the time, sleeping {seconds} per batch.',
                    ['delay' => $date, 'batch_size' => 1000, 'seconds' => 0],
                ]
            )
            ->shouldBeCalledOnce();

        $this->loggerProphecy
            ->__call(
                'debug',
                [
                    'Sleeping for {seconds} seconds during purge.',
                    ['seconds' => 0],
                ]
            )
            ->shouldBeCalledOnce();

        $this->loggerProphecy
            ->__call(
                'debug',
                [
                    'Successfully purged {record_count} records.',
                    ['record_count' => 1002],
                ]
            )
            ->shouldBeCalledOnce();

        $this->connectionProphecy
            ->__call(
                'executeUpdate',
                [
                    'DELETE FROM command__execution WHERE state = ? AND updated_at < ? LIMIT ?',
                    ['terminated', new \DateTime($date), 1000],
                    ['string', 'datetime', 'integer'],
                ]
            )
            ->shouldBeCalledTimes(2)
            ->willReturn(1000, 2);

        $this->execute(['--delay' => $date, '--sleep' => 0])
            ->test(CommandDataTester::create());
    }
}
