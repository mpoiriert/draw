<?php

namespace Draw\Component\EntityMigrator\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Draw\Component\EntityMigrator\Workflow\MigrationWorkflow;

#[
    ORM\Entity,
    ORM\Table(name: 'draw_entity_migrator__migration'),
    ORM\UniqueConstraint(name: 'name', columns: ['name'])
]
class Migration implements \Stringable
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(type: Types::INTEGER)
    ]
    private int $id;

    #[
        ORM\Column(type: Types::STRING, length: 255, nullable: false)
    ]
    private string $name;

    #[
        ORM\Column(type: Types::STRING, length: 255, nullable: false)
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
        return MigrationWorkflow::PLACE_PAUSED === $this->state;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
