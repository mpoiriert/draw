<?php

namespace Draw\Component\Process;

use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Process;

/**
 * This class allow to injection to create a process.
 */
class ProcessFactory implements ProcessFactoryInterface
{
    /**
     * @param array       $command The command to run and its arguments listed as separate entries
     * @param string|null $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null  $env     The environment variables or null to use the same environment as the current PHP process
     * @param mixed       $input   The input as stream resource, scalar or \Traversable, or null for no input
     * @param float|null  $timeout The timeout in seconds or null to disable
     *
     * @throws LogicException When proc_open is not installed
     */
    public function create(array $command, ?string $cwd = null, ?array $env = null, $input = null, ?float $timeout = 60): Process
    {
        return new Process(...\func_get_args());
    }

    public function createFromShellCommandLine(
        string $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?float $timeout = 60,
    ): Process {
        return Process::fromShellCommandline(...\func_get_args());
    }
}
