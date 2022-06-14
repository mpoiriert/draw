<?php

namespace App\Tests\Command;

use App\Command\NullCommand;
use App\Tests\TestCase;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @covers \App\Command\NullCommand
 */
class NullCommandTest extends TestCase
{
    use CommandTestTrait;

    public function createCommand(): Command
    {
        return static::getService(NullCommand::class);
    }

    public function getCommandName(): string
    {
        return 'app:null';
    }

    public function provideTestArgument(): iterable
    {
        return [];
    }

    public function provideTestOption(): iterable
    {
        yield [
            'draw-execution-id',
            null,
            InputOption::VALUE_REQUIRED,
        ];

        yield [
            'draw-execution-ignore',
            null,
            InputOption::VALUE_OPTIONAL,
        ];

        yield [
            'aws-newest-instance-role',
            null,
            InputOption::VALUE_REQUIRED,
        ];
    }

    public function testExecute(): void
    {
        $this->execute([])
            ->test(CommandDataTester::create()->setExpectedDisplay('This does nothing.'));
    }
}
