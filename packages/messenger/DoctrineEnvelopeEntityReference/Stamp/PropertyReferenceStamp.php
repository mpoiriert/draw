<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class PropertyReferenceStamp implements StampInterface
{
    public function __construct(
        private string $propertyName,
        private string $class,
        private array $identifiers,
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }
}
