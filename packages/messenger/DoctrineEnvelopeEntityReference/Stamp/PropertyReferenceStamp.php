<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class PropertyReferenceStamp implements StampInterface
{
    /**
     * @var string|int
     */
    private $key;

    private string $class;

    private array $identifiers;

    public function __construct($key, string $class, array $identifiers)
    {
        $this->key = $key;
        $this->class = $class;
        $this->identifiers = $identifiers;
    }

    /**
     * @return int|string
     */
    public function getKey()
    {
        return $this->key;
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
