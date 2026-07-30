<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ValueIsNotUsed extends Constraint
{
    public const string CODE = 'VALUE_ALREADY_TAKEN';

    public string $message = 'Value "{{ value }}" is already used.';

    #[HasNamedArguments]
    public function __construct(
        public string $entityClass,
        public string $field,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct(
            groups: $groups,
            payload: $payload
        );
    }

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
