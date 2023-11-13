<?php

namespace App\Tests\Console\Command;

use App\Tests\FilteredCommandTestTrait;
use App\Tests\TestCase;
use Draw\Component\Console\Command\GenerateDocumentationCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @covers \Draw\Component\Console\Command\GenerateDocumentationCommand
 */
class GenerateDocumentationCommandTest extends TestCase
{
    use FilteredCommandTestTrait;

    private bool $writeFile = false;

    public function createCommand(): Command
    {
        return static::getContainer()->get(GenerateDocumentationCommand::class);
    }

    public function getCommandName(): string
    {
        return 'draw:console:generate-documentation';
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
            'format',
            null,
            InputOption::VALUE_REQUIRED,
            'txt',
        ];
    }

    public function testExecute(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'testExecute');

        register_shutdown_function(
            function () use ($filePath): void {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        );

        $this->execute(['path' => $filePath])
            ->test(
                CommandDataTester::create()
                    ->setExpectedDisplay([
                        'Generate documentation',
                        'Export completed',
                    ])
                    ->setExpectedErrorOutput(null)
            );

        if ($this->writeFile) {
            file_put_contents(
                __DIR__.'/fixtures/GenerateDocumentationCommandTest/testExecution_expectedExport.txt',
                file_get_contents($filePath)
            );
        }

        static::assertFileEquals(
            __DIR__.'/fixtures/GenerateDocumentationCommandTest/testExecution_expectedExport.txt',
            $filePath
        );
    }

    public function testWriteFile(): void
    {
        static::assertFalse(
            $this->writeFile,
            'Do not forget to put this variable back to false.'
        );
    }
}
