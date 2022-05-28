<?php

namespace App\Tests\Command;

use App\Command\NullCommand;
use App\Tests\TestCase;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Symfony\Component\Console\Command\Command;

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

    public function getCommandDescription(): string
    {
        return 'This command does nothing.';
    }

    public function provideTestArgument(): iterable
    {
        return [];
    }

    public function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $this->execute([])
            ->test(CommandDataTester::create()->setExpectedDisplay('This does nothing.'));
    }

    protected function filterDefinitionOptions(array $options): array
    {
        unset($options['draw-execution-id']);
        unset($options['draw-execution-ignore']);

        return $options;
    }
}
