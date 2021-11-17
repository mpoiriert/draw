<?php

namespace Draw\Bundle\CommandBundle;

use Draw\Bundle\CommandBundle\Model\Command;

class CommandRegistry
{
    /**
     * @var Command[]
     */
    private $commands = [];

    public function setCommand(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * @return Command[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getCommand(string $name): ?Command
    {
        return $this->commands[$name] ?? null;
    }
}
