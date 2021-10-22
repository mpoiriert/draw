<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConstraintRemoteFileExists extends Constraint
{
    public $message = 'Remote file "{{ value }}" does not exist.';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
