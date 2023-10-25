<?php

namespace Draw\Component\EntityMigrator\Entity;

use Draw\Component\EntityMigrator\MigrationTargetEntityInterface;

interface EntityMigrationInterface
{
    public function getId(): ?int;

    public function getEntity(): MigrationTargetEntityInterface;

    public function getMigration(): Migration;

    public function getState(): string;

    public function setState(string $state): static;
}
