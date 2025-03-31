<?php

namespace Draw\Component\DataSynchronizer\Import;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class RestorationData
{
    public function __construct(
        private string $class,
        private array $data,
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
