<?php

declare(strict_types=1);

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireClient
{
    public function __construct(
        private array $options = [],
        private array $server = [],
    ) {
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getServer(): array
    {
        return $this->server;
    }
}
