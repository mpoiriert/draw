<?php

namespace Draw\Component\Process\Tests;

use Draw\Component\Process\ProcessFactory;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[
    CoversClass(ProcessFactory::class),
    CoversClass(ProcessFactoryInterface::class),
]
class ProcessFactoryTest extends TestCase
{
    private ProcessFactory $service;

    protected function setUp(): void
    {
        $this->service = new ProcessFactory();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ProcessFactoryInterface::class,
            $this->service
        );
    }

    public function testCreateDefault(): void
    {
        $process = $this->service->create(['cd']);

        static::assertInstanceOf(Process::class, $process);

        static::assertSame(
            "'cd'",
            $process->getCommandLine()
        );

        static::assertSame(
            getcwd(),
            $process->getWorkingDirectory()
        );

        static::assertEmpty(
            $process->getEnv()
        );

        static::assertNull(
            $process->getInput()
        );

        static::assertSame(
            60.0,
            $process->getTimeout()
        );
    }

    public function testCreateWithArguments(): void
    {
        $process = $this->service->create(
            ['cd'],
            $workingDirectory = __DIR__,
            $env = ['key' => 'value'],
            $input = 'input',
            $timeout = 5.0
        );

        static::assertInstanceOf(Process::class, $process);

        static::assertSame(
            "'cd'",
            $process->getCommandLine()
        );

        static::assertSame(
            $workingDirectory,
            $process->getWorkingDirectory()
        );

        static::assertSame(
            $env,
            $process->getEnv()
        );

        static::assertSame(
            $input,
            $process->getInput()
        );

        static::assertSame(
            $timeout,
            $process->getTimeout()
        );
    }

    public function testCreateFromShellCommandLineDefault(): void
    {
        $process = $this->service->createFromShellCommandLine('ls -lah | grep test');

        static::assertInstanceOf(Process::class, $process);
        static::assertSame('ls -lah | grep test', $process->getCommandLine());
        static::assertSame(getcwd(), $process->getWorkingDirectory());
        static::assertEmpty($process->getEnv());
        static::assertNull($process->getInput());
        static::assertSame(60.0, $process->getTimeout());
    }

    public function testCreateFromShellCommandLineWithArguments(): void
    {
        $process = $this->service->createFromShellCommandLine(
            'ls -lah | grep test',
            $workingDirectory = __DIR__,
            $env = ['key' => 'value'],
            $input = 'input',
            $timeout = 5.0
        );

        static::assertInstanceOf(Process::class, $process);
        static::assertSame('ls -lah | grep test', $process->getCommandLine());
        static::assertSame($workingDirectory, $process->getWorkingDirectory());
        static::assertSame($env, $process->getEnv());
        static::assertSame($input, $process->getInput());
        static::assertSame($timeout, $process->getTimeout());
    }
}
