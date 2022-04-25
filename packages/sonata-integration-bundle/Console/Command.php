<?php

namespace Draw\Bundle\SonataIntegrationBundle\Console;

class Command
{
    private string $name;

    private string $commandName;

    private ?string $label;

    private ?string $icon;

    public function __construct(string $name, string $commandName, ?string $label = null, ?string $icon = null)
    {
        $this->name = $name;
        $this->commandName = $commandName;
        $this->label = $label;
        $this->icon = $icon;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
