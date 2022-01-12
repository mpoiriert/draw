<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Throwable;

/**
 * Validate a php callable constraint.
 */
class PhpCallableValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PhpCallable) {
            throw new UnexpectedTypeException($constraint, PhpCallable::class);
        }

        if (null === $value && $constraint->ignoreNull) {
            return;
        }

        try {
            $result = \call_user_func($constraint->callable, $value);
            if ($constraint->returnValueConstraint) {
                $violations = $this->context->getValidator()->validate($result, $constraint->returnValueConstraint);
                if (!\count($violations)) {
                    return;
                }
            } else {
                return;
            }
        } catch (Throwable $throwable) {
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->addViolation();
    }
}
