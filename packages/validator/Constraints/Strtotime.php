<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Assertion that the string value is compatible with strtotime in php.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class Strtotime extends PhpCallable
{
    public ?string $message = 'The value {{ value }} is not valid to use in strtotime.';

    #[HasNamedArguments]
    public function __construct(
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(
            callable: 'strtotime',
            returnValueConstraint: new Type('int'),
            message: $message,
            groups: $groups,
            payload: $payload,
        );
    }
}
