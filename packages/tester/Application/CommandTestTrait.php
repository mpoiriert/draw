<?php

namespace Draw\Component\Tester\Application;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

trait CommandTestTrait
{
    private static $argumentsCount;

    protected CommandTester $commandTester;

    protected Command $command;

    abstract public function createCommand(): Command;

    abstract public function getCommandName(): string;

    abstract public function getCommandDescription(): string;

    abstract public function provideTestArgument(): iterable;

    abstract public function provideTestOption(): iterable;

    public static function setUpBeforeClass(): void
    {
        self::$argumentsCount = 0;
    }

    public function setUp(): void
    {
        $application = new Application();
        $application->add($this->createCommand());
        $this->command = $application->find($this->getCommandName());
        $this->commandTester = new CommandTester($this->command);
    }

    public function testGetDescription()
    {
        TestCase::assertSame($this->getCommandDescription(), $this->command->getDescription());
    }

    public function testArguments(): void
    {
        $definition = $this->command->getDefinition();

        $position = 0;
        foreach ($this->provideTestArgument() as $argument) {
            array_unshift($argument, $this->command, $position);
            call_user_func_array([$this, 'assertArgument'], $argument);
            ++$position;
        }

        TestCase::assertSame(
            $definition->getArgumentCount(),
            $position,
            'Argument count does not match'
        );
    }

    public function assertArgument(
        Command $command,
        int $position,
        string $name,
        ?int $mode,
        string $description = null,
        $default = null
    ): void {
        $definition = $command->getDefinition();

        TestCase::assertTrue($definition->hasArgument($position), 'No argument at this position');

        $argument = $definition->getArgument($position);
        TestCase::assertSame(
            $name,
            $argument->getName(),
            'Argument at position ['.$position.'] does not match the name.'
        );
        TestCase::assertSame($default, $argument->getDefault());
        TestCase::assertSame($description, $argument->getDescription());

        if (InputArgument::IS_ARRAY & $mode) {
            TestCase::assertTrue($argument->isArray());
        }

        if (InputArgument::OPTIONAL & $mode) {
            TestCase::assertFalse($argument->isRequired());
        }

        if (InputArgument::REQUIRED & $mode) {
            TestCase::assertTrue($argument->isRequired());
        }
    }

    public function testOptions(): void
    {
        $count = 0;
        foreach ($this->provideTestOption() as $option) {
            ++$count;
            array_unshift($option, $this->command);
            call_user_func_array([$this, 'assertOption'], $option);
        }

        $definition = $this->command->getDefinition();
        TestCase::assertSame(
            count($definition->getOptions()),
            $count,
            'Options count does not match'
        );
    }

    public function assertOption(
        Command $command,
        string $name,
        ?string $shortcut,
        ?int $mode,
        string $description,
        $default = null
    ): void {
        $definition = $command->getDefinition();

        $option = $definition->getOption($name);

        TestCase::assertSame($shortcut, $option->getShortcut());

        if (null === $default && !$option->acceptValue()) {
            $default = false;
        }

        TestCase::assertSame($default, $option->getDefault());
        TestCase::assertSame($description, $option->getDescription());

        if (InputOption::VALUE_IS_ARRAY & $mode) {
            TestCase::assertTrue($option->isArray());
        }

        if (InputOption::VALUE_OPTIONAL & $mode) {
            TestCase::assertFalse($option->isValueRequired());
        }

        if (InputOption::VALUE_REQUIRED & $mode) {
            TestCase::assertTrue($option->isValueRequired());
        }

        TestCase::assertSame(!(InputOption::VALUE_NONE & $mode), $option->acceptValue());
    }

    /**
     * Executes the command.
     *
     * Available execution options:
     *
     *  * interactive:               Sets the input interactive flag
     *  * decorated:                 Sets the output decorated flag
     *  * verbosity:                 Sets the output verbosity flag
     *  * capture_stderr_separately: Make output of stdOut and stdErr separately available
     *
     * @param array $input   An array of command arguments and options
     * @param array $options An array of execution options
     *
     * @return DataTester The data tester with the command tester as data
     *
     * @see CommandDataTester
     */
    public function execute(array $input, array $options = []): DataTester
    {
        $columns = getenv('COLUMNS');
        putenv('COLUMNS=120');
        $options += ['capture_stderr_separately' => true];
        $this->commandTester->execute($input, $options);
        putenv('COLUMNS='.$columns);

        return new DataTester($this->commandTester);
    }
}
