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
     *
     * @var string
     */
    public $message = 'Execution of function with {{ value }} does not return expected result.';

    /**
     * The php callable.
     *
     * @var string
     */
    public $callable;

    /**
     * If we must validate null value or not.
     *
     * @var bool
     */
    public $ignoreNull = true;

    /**
     * A constraint to validate the return value of the callable.
     *
     * Some callable will throw a exception other will return false, null or predefined value when input is invalid
     *
     * @var Constraint|null
     */
    public $returnValueConstraint;

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'callable';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions(): array
    {
        return ['callable'];
    }

    public function validatedBy(): string
    {
        return PhpCallableValidator::class;
    }
}
