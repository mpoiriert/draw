<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Type;

/**
 * Assertion that the string value is compatible with strtotime in php.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class Strtotime extends PhpCallable
{
    public ?string $message = 'The value {{ value }} is not valid to use in strtotime.';

    public $callable = 'strtotime';

    public function __construct($options = null)
    {
        parent::__construct(['callable' => $this->callable] + (array) $options);
        $this->returnValueConstraint = new Type(['type' => 'int']);
    }
}
