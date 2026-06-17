<?php

namespace Draw\Component\DataSynchronizer\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PreDeleteEntityEvent extends Event
{
    private bool $allowDelete = true;

    public function __construct(
        private object $entity,
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function isAllowDelete(): bool
    {
        return $this->allowDelete;
    }

    public function preventDelete(): void
    {
        $this->allowDelete = false;
        $this->stopPropagation();
    }
}
