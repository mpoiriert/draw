<?php

namespace App\Tests\Console\Command;

use App\Tests\TestCase;
use Draw\Component\Console\Command\ExportListCommandsCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @covers \App\Command\ExportListCommandsCommand
 */
class ExportListCommandsCommandTest extends TestCase
{
    use CommandTestTrait;

    public function createCommand(): Command
    {
        return static::getContainer()->get(ExportListCommandsCommand::class);
    }

    public function getCommandName(): string
    {
        return 'app:list-commands';
    }

    public static function provideTestArgument(): iterable
    {
        return [['Path' => __DIR__.'/Fixtures/ExportListCommandsCommandTest/'], ['Filename' => 'testExecution.txt']];

    }

    public static function provideTestOption(): iterable
    {
        yield [
            'draw-execution-id',
            null,
            InputOption::VALUE_REQUIRED,
        ];

        yield [
            'draw-execution-ignore',
            null,
            InputOption::VALUE_NONE,
        ];

        yield [
            'aws-newest-instance-role',
            null,
            InputOption::VALUE_REQUIRED,
        ];
    }

    public function testExecute(): void
    {
        $this->execute(['Path' => __DIR__.'/Fixtures/ExportListCommandsCommandTest/', 'Filename' => 'testExecution.txt'])
            ->test(
                CommandDataTester::create()
                    ->setExpectedDisplay($this->getDefaultExpectation())
            );
    }

    private function getDefaultExpectation(): string
    {
        return file_get_contents(__DIR__.'/Fixtures/ExportListCommandsCommandTest/testExecution_expectedExport.txt');
    }
}
