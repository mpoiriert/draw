<?php

namespace Draw\Component\Process\Tests;

use Draw\Component\Process\ProcessFactory;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends TestCase
{
    private ProcessFactory $service;

    public function setUp(): void
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
}
