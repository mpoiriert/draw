<?php

namespace Draw\Bundle\CommandBundle;

use Draw\Bundle\CommandBundle\Model\Command;

class CommandRegistry
{
    /**
     * @var Command[]
     */
    private $commands;

    public function setCommand(Command $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param $name
     *
     * @return Command
     */
    public function getCommand($name)
    {
        return $this->commands[$name] ?? null;
    }
}
