<?php

namespace Draw\Contracts\Process;

use Symfony\Component\Process\Process;

interface ProcessFactoryInterface
{
    public function create(array $command, ?string $cwd = null, ?array $env = null, $input = null, ?float $timeout = 60): Process;

    public function createFromShellCommandLine(
        string $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?float $timeout = 60,
    ): Process;
}
