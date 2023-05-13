<?php

namespace Draw\Component\OpenApi;

class Scope
{
    public function __construct(private string $name, private ?array $tags)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }
}
