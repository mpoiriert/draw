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
        $this->assertInstanceOf(
            ProcessFactoryInterface::class,
            $this->service
        );
    }

    public function testCreateDefault(): void
    {
        $process = $this->service->create(['cd']);

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame(
            "'cd'",
            $process->getCommandLine()
        );

        $this->assertSame(
            getcwd(),
            $process->getWorkingDirectory()
        );

        $this->assertEmpty(
            $process->getEnv()
        );

        $this->assertNull(
            $process->getInput()
        );

        $this->assertSame(
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

        $this->assertInstanceOf(Process::class, $process);

        $this->assertSame(
            "'cd'",
            $process->getCommandLine()
        );

        $this->assertSame(
            $workingDirectory,
            $process->getWorkingDirectory()
        );

        $this->assertSame(
            $env,
            $process->getEnv()
        );

        $this->assertSame(
            $input,
            $process->getInput()
        );

        $this->assertSame(
            $timeout,
            $process->getTimeout()
        );
    }
}
