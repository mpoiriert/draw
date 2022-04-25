<?php

namespace Draw\Bundle\SonataIntegrationBundle\Console;

class CommandRegistry
{
    /**
     * @var Command[]
     */
    private array $commands = [];

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
