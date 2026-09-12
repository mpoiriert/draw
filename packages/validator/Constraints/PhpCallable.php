<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * A base class to do assertion base on php callable.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class PhpCallable extends Constraint
{
    /**
     * The default message.
     */
    public ?string $message = 'Execution of function with {{ value }} does not return expected result.';

    /**
     * @param callable        $callable              the php callable
     * @param bool            $ignoreNull            if we must validate null value or not
     * @param Constraint|null $returnValueConstraint A constraint to validate the return value of the callable. Some callable will throw a exception other will return false, null or predefined value when input is invalid.
     * @param string|null     $message               the violation message, defaulting to the one declared by the constraint
     */
    #[HasNamedArguments]
    public function __construct(
        public mixed $callable,
        public ?Constraint $returnValueConstraint = null,
        public bool $ignoreNull = true,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(
            groups: $groups,
            payload: $payload
        );

        if (null !== $message) {
            $this->message = $message;
        }
    }

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    final public function validatedBy(): string
    {
        return PhpCallableValidator::class;
    }
}
