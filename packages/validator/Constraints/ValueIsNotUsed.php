<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ValueIsNotUsed extends Constraint
{
    public const CODE = 'VALUE_ALREADY_TAKEN';

    public function __construct(
        public string $entityClass,
        public string $field,
        public string $message = 'Value "{{ value }}" is already used.',
        ?array $groups = null,
        $payload = null,
        array $options = [],
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
