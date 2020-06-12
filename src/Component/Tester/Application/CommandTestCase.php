<?php

namespace Draw\Component\Tester\Application;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends TestCase
{
    private static $argumentsCount;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var Command
     */
    private $command;

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
        $this->assertSame($this->getCommandDescription(), $this->command->getDescription());
    }

    /**
     * @dataProvider provideTestArgument
     *
     * @param $name
     * @param $mode
     * @param $description
     * @param $default
     */
    public function testArgument(string $name, ?int $mode, string $description, $default = null)
    {
        $argumentPosition = self::$argumentsCount;
        ++self::$argumentsCount;
        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasArgument($argumentPosition), 'No argument at this position');

        $argument = $definition->getArgument($argumentPosition);
        $this->assertSame(
            $name,
            $argument->getName(),
            'Argument at position ['.$argumentPosition.'] does not match the name.'
        );
        $this->assertSame($default, $argument->getDefault());
        $this->assertSame($description, $argument->getDescription());

        if (InputArgument::IS_ARRAY & $mode) {
            $this->assertTrue($argument->isArray());
        }

        if (InputArgument::OPTIONAL & $mode) {
            $this->assertFalse($argument->isRequired());
        }

        if (InputArgument::REQUIRED & $mode) {
            $this->assertTrue($argument->isRequired());
        }
    }

    /**
     * @dataProvider provideTestOption
     *
     * @param $name
     * @param $shortcut
     * @param $mode
     * @param $description
     * @param $default
     */
    public function testOption($name, $shortcut, ?int $mode, string $description, $default = null)
    {
        $definition = $this->command->getDefinition();
        $option = $definition->getOption($name);

        $this->assertSame($shortcut, $option->getShortcut());

        if (null === $default && !$option->acceptValue()) {
            $default = false;
        }

        $this->assertSame($default, $option->getDefault());
        $this->assertSame($description, $option->getDescription());

        if (InputOption::VALUE_IS_ARRAY & $mode) {
            $this->assertTrue($option->isArray());
        }

        if (InputOption::VALUE_OPTIONAL & $mode) {
            $this->assertFalse($option->isValueRequired());
        }

        if (InputOption::VALUE_REQUIRED & $mode) {
            $this->assertTrue($option->isValueRequired());
        }

        $this->assertSame(!(InputOption::VALUE_NONE & $mode), $option->acceptValue());
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
        $options += ['capture_stderr_separately' => true];
        $this->commandTester->execute($input, $options);

        return new DataTester($this->commandTester);
    }
}
