<?php

namespace Draw\Component\EntityMigrator\Entity;

use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'draw_entity_migrator__migration'),
    ORM\UniqueConstraint(name: 'name', columns: ['name'])
]
class Migration
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(type: 'integer')
    ]
    private int $id;

    #[
        ORM\Column(type: 'string', length: 255, nullable: false)
    ]
    private string $name;

    #[
        ORM\Column(type: 'string', length: 255, nullable: false)
    ]
    private string $state;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function isPaused(): bool
    {
        return 'paused' === $this->state;
    }
}
