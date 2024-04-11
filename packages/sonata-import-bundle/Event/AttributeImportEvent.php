<?php

namespace Draw\Bundle\SonataImportBundle\Event;

use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Contracts\EventDispatcher\Event;

class AttributeImportEvent extends Event
{
    public function __construct(
        /**
         * A doctrine entity.
         */
        private object $entity,
        private Column $column,
        private mixed $value
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getColumn(): Column
    {
        return $this->column;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
