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
    private static int $argumentsCount;

    protected ?CommandTester $commandTester = null;

    protected ?Command $command = null;

    private ?Application $application = null;

    protected function createCommand(): Command
    {
        if (null === $this->command) {
            throw new \RuntimeException('You must override createCommand method or set the command property.');
        }

        return $this->command;
    }

    final protected function loadCommand(): Command
    {
        $command = $this->command ??= $this->createCommand();

        if (null === $this->application) {
            $this->application = new Application();
            $this->application->add($command);
        }

        return $command;
    }

    final protected function loadCommandTester(): CommandTester
    {
        return $this->commandTester ??= new CommandTester($this->loadCommand());
    }

    abstract public function getCommandName(): string;

    abstract public static function provideTestArgument(): iterable;

    abstract public static function provideTestOption(): iterable;

    public static function setUpBeforeClass(): void
    {
        self::$argumentsCount = 0;
    }

    protected function getMinimumCommandDescriptionStringLength(): int
    {
        return 10;
    }

    protected function getMinimumInputDescriptionStringLength(): int
    {
        return 10;
    }

    public function testGetCommandName(): void
    {
        $command = $this->loadCommand();

        TestCase::assertSame(
            $command,
            $this->application->find($this->getCommandName()),
            'Cannot find the command by name'
        );
    }

    public function testGetDescription(): void
    {
        TestCase::assertGreaterThanOrEqual(
            $this->getMinimumCommandDescriptionStringLength(),
            mb_strlen($this->loadCommand()->getDescription()),
            'Command description is too short'
        );
    }

    public function testArguments(): void
    {
        $command = $this->loadCommand();
        $definition = $command->getDefinition();

        $position = 0;
        foreach ($this->provideTestArgument() as $argument) {
            array_unshift($argument, $command, $position);
            \call_user_func_array([$this, 'assertArgument'], $argument);
            ++$position;
        }

        TestCase::assertSame(
            // We use count instead of InputDefinition::getArgumentCounts to support array argument
            \count($definition->getArguments()),
            $position,
            'Argument count does not match'
        );
    }

    public function assertArgument(
        Command $command,
        int $position,
        string $name,
        ?int $mode,
        $default = null,
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

        TestCase::assertGreaterThanOrEqual(
            $this->getMinimumInputDescriptionStringLength(),
            mb_strlen($argument->getDescription()),
            'Argument description is too short'
        );

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
        $command = $this->loadCommand();
        $definition = $command->getDefinition();
        $realCommandOptions = [];
        foreach ($definition->getOptions() as $option) {
            if (null !== $shortcut = $option->getShortcut()) {
                TestCase::assertEquals(1, \strlen($shortcut));
            }

            $realCommandOptions[$option->getName()] = $option;
        }

        foreach ($this->provideTestOption() as $optionConfiguration) {
            unset($realCommandOptions[$optionConfiguration[0]]);
            ++$count;
            array_unshift($optionConfiguration, $command);
            \call_user_func_array([$this, 'assertOption'], $optionConfiguration);
        }

        TestCase::assertSame(
            [],
            array_keys($this->filterDefinitionOptions($realCommandOptions)),
            'Those options are not accounted for.'
        );
    }

    /**
     * @param InputOption[] $options
     *
     * @return InputOption[]
     */
    protected function filterDefinitionOptions(array $options): array
    {
        return $options;
    }

    public function assertOption(
        Command $command,
        string $name,
        ?string $shortcut,
        ?int $mode,
        $default = null,
    ): void {
        $definition = $command->getDefinition();

        $option = $definition->getOption($name);

        TestCase::assertSame($shortcut, $option->getShortcut());

        if (null === $default && !$option->acceptValue()) {
            $default = false;
        }

        TestCase::assertSame($default, $option->getDefault());

        TestCase::assertGreaterThanOrEqual(
            $this->getMinimumInputDescriptionStringLength(),
            mb_strlen($option->getDescription()),
            'Option description is too short'
        );

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
        $commandTester = $this->loadCommandTester();
        $columns = getenv('COLUMNS');
        putenv('COLUMNS=120');
        $options += ['capture_stderr_separately' => true];
        $commandTester->execute($input, $options);
        putenv('COLUMNS='.$columns);

        return new DataTester($commandTester);
    }
}
