<?php

namespace App\Tests\Command;

use App\Command\ExportListCommandsCommand;
use App\Tests\TestCase;
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
        return static::getService(ExportListCommandsCommand::class);
    }

    public function getCommandName(): string
    {
        return 'app:list-commands';
    }

    public static function provideTestArgument(): iterable
    {
        return [];
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
        $this->execute([])
            ->test(
                CommandDataTester::create()
                    ->setExpectedDisplay($this->getDefaultExpectation())
            );
    }

    private function getDefaultExpectation(): string
    {
        $jsonContent = file_get_contents(__DIR__.'/result/defaultExport.json');

        return $jsonContent;
    }
}
