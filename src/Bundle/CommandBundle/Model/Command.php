<?php

namespace Draw\Bundle\CommandBundle\Model;

class Command
{
    private $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getIcon(): ?string
    {
        return $this->configuration['icon'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->configuration['name'] ?? null;
    }

    public function getCommandName(): ?string
    {
        return $this->configuration['commandName'] ?? null;
    }

    public function getLabel(): ?string
    {
        return $this->configuration['label'] ?? null;
    }
}
