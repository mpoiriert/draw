<?php

namespace Draw\Component\Core\FilterExpression\Expression;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintExpression extends Expression
{
    /**
     * If no constraint is passed, the constraint
     * {@link \Symfony\Component\Validator\Constraints\Valid} is assumed.
     *
     * @param Constraint|Constraint[]|null                          $constraints The constraint(s) to validate against
     * @param string|GroupSequence|array<string|GroupSequence>|null $groups      The validation groups to validate. If none is given, "Default" is assumed
     *
     * @see ValidatorInterface::validate()
     */
    public function __construct(
        private ?string $path,
        private $constraints = null,
        private $groups = null,
    ) {
    }

    /**
     * @return Constraint|Constraint[]|null
     */
    public function getConstraints(): Constraint|array|null
    {
        return $this->constraints;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return string|GroupSequence|array<string|GroupSequence>|null
     */
    public function getGroups(): string|GroupSequence|array|null
    {
        return $this->groups;
    }
}
