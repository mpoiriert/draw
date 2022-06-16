<?php

namespace Draw\Component\Process;

use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\Process\Process;

/**
 * This class allow to injection to create a process.
 */
class ProcessFactory implements ProcessFactoryInterface
{
    public function create(array $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60): Process
    {
        return new Process(...\func_get_args());
    }
}
