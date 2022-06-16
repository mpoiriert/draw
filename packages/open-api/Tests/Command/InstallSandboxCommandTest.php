<?php

namespace Draw\Component\OpenApi\Tests\Command;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\OpenApi\Command\InstallSandboxCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \Draw\Component\OpenApi\Command\InstallSandboxCommand
 */
class InstallSandboxCommandTest extends TestCase
{
    use CommandTestTrait;

    public function createCommand(): Command
    {
        return new InstallSandboxCommand();
    }

    public function getCommandName(): string
    {
        return 'draw:open-api:install-sandbox';
    }

    public function provideTestArgument(): iterable
    {
        yield [
            'path',
            InputArgument::REQUIRED,
        ];
    }

    public function provideTestOption(): iterable
    {
        yield [
            'tag',
            null,
            InputOption::VALUE_REQUIRED,
            'master',
        ];
    }

    public function testExecute(): void
    {
        $path = sys_get_temp_dir().'/sandbox';

        register_shutdown_function(function () use ($path) {
            if (is_dir($path)) {
                (new Filesystem())->remove($path);
            }
        });

        $this
            ->execute(
                [
                    'path' => $path,
                    '--tag' => 'v3.52.5',
                ]
            )
            ->test(
                CommandDataTester::create(
                    0,
                    "Downloading Swagger UI... Ok.\n".
                    "Extracting zip file... Ok.\n"
                )
            );

        static::assertDirectoryExists($path);
    }

    public function testExecuteZipError(): void
    {
        $path = sys_get_temp_dir().'/sandbox';

        register_shutdown_function(function () use ($path) {
            if (is_dir($path)) {
                (new Filesystem())->remove($path);
            }
        });

        ReflectionAccessor::setPropertyValue(
            $this->command,
            'filesystem',
            $filesystem = $this->createMock(Filesystem::class)
        );

        $filesystem
            ->expects(static::once())
            ->method('dumpFile');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot open zip file [/tmp/swagger-ui-v3.52.5.zip]. Error code [9].');

        $this
            ->execute(
                [
                    'path' => $path,
                    '--tag' => 'v3.52.5',
                ]
            )
            ->test(
                CommandDataTester::create(
                    0,
                    "Downloading Swagger UI... Ok.\n".
                    "Extracting zip file... Ok.\n"
                )
            );
    }
}
