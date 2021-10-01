<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ConstraintRemoteFileExistsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ConstraintRemoteFileExists) {
            throw new UnexpectedTypeException($constraint, ConstraintRemoteFileExists::class);
        }

        if (!$this->remoteFileExists($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }

    public function remoteFileExists($url): bool
    {
        if ($handle = @fopen($url, 'r')) {
            fclose($handle);

            return true;
        }

        return false;
    }
}
