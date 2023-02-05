<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class RemoteFileExists extends Constraint
{
    public ?string $message = 'Remote file "{{ value }}" does not exist.';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
