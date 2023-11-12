<?php

namespace Draw\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event when we generate the documentation of the application.
 * It's dispatched for every command.
 *
 * You can ignore the documentation of a command by calling the ignore method.
 */
class GenerateDocumentationEvent extends Event
{
    private bool $ignore = false;

    public function __construct(private Command $command)
    {
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function ignore(): void
    {
        $this->ignore = true;
        $this->stopPropagation();
    }

    public function isIgnored(): bool
    {
        return $this->ignore;
    }
}
