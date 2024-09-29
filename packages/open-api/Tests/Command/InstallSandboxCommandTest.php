<?php

namespace Draw\Component\OpenApi\Tests\Command;

use Draw\Component\OpenApi\Command\InstallSandboxCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(InstallSandboxCommand::class)]
class InstallSandboxCommandTest extends TestCase
{
    use CommandTestTrait;
    use MockTrait;

    protected function setUp(): void
    {
        $this->command = new InstallSandboxCommand();
    }

    public function getCommandName(): string
    {
        return 'draw:open-api:install-sandbox';
    }

    public static function provideTestArgument(): iterable
    {
        yield [
            'path',
            InputArgument::REQUIRED,
        ];
    }

    public static function provideTestOption(): iterable
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

        register_shutdown_function(static function () use ($path): void {
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
            )
        ;

        static::assertDirectoryExists($path);
    }

    public function testExecuteZipError(): void
    {
        $path = sys_get_temp_dir().'/sandbox';

        register_shutdown_function(static function () use ($path): void {
            if (is_dir($path)) {
                (new Filesystem())->remove($path);
            }
        });

        $filesystem = $this->mockProperty(
            $this->command,
            'filesystem',
            Filesystem::class
        );

        $filesystem
            ->expects(static::once())
            ->method('dumpFile')
        ;

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
            )
        ;
    }
}
