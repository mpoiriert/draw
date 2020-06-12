<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class EntityParameters implements ParametersInterface
{
    /**
     * @var string|null
     */
    private $class;

    /**
     * @var array<string>
     */
    private $fields = ['id'];

    public function __construct(array $values = [])
    {
        construct($this, $values);
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): void
    {
        $this->class = $class;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function toArray(): array
    {
        return [
            '_class' => $this->getClass(),
            '_fields' => $this->getFields(),
        ];
    }
}
