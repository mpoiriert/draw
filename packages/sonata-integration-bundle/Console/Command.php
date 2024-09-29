<?php

namespace Draw\Bundle\SonataIntegrationBundle\Console;

class Command
{
    public function __construct(
        private string $name,
        private string $commandName,
        private ?string $label = null,
        private ?string $icon = null,
    ) {
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
