<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * A base class to do assertion base on php callable.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class PhpCallable extends Constraint
{
    /**
     * The default message.
     */
    public ?string $message = 'Execution of function with {{ value }} does not return expected result.';

    /**
     * The php callable.
     *
     * @var callable
     */
    public $callable = null;

    /**
     * If we must validate null value or not.
     */
    public bool $ignoreNull = true;

    /**
     * A constraint to validate the return value of the callable.
     *
     * Some callable will throw a exception other will return false, null or predefined value when input is invalid
     */
    public ?Constraint $returnValueConstraint = null;

    public function getDefaultOption(): ?string
    {
        return 'callable';
    }

    public function getRequiredOptions(): array
    {
        return ['callable'];
    }

    final public function validatedBy(): string
    {
        return PhpCallableValidator::class;
    }
}
