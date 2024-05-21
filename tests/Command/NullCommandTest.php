<?php

namespace App\Tests\Command;

use App\Command\NullCommand;
use App\Tests\TestCase;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class NullCommandTest extends TestCase implements AutowiredInterface
{
    use CommandTestTrait;

    #[AutowireService(NullCommand::class)]
    protected ?Command $command = null;

    public function getCommandName(): string
    {
        return 'app:null';
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
                    ->setExpectedDisplay('This does nothing.')
            );
    }
}
