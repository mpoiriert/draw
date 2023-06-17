<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class RemoteFileExists extends Constraint
{
    public ?string $message = 'Remote file "{{ value }}" does not exist.';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
