<?php namespace Draw\Bundle\CommandBundle;

class CommandFactory
{
    /**
     * @var Command[]
     */
    private $commands;

    public function addCommand(Command $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @param $name
     * @return Command
     */
    public function getCommand($name)
    {
        return $this->commands[$name] ?? null;
    }
}