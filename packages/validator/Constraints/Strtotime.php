<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Type;

/**
 * Assertion that the string value is compatible with strtotime in php.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Strtotime extends PhpCallable
{
    public $callable = 'strtotime';

    public $message = 'The value {{ value }} is not valid to use in strtotime.';

    public function __construct($options = null)
    {
        parent::__construct(['callable' => $this->callable] + (array) $options);
        $this->returnValueConstraint = new Type(['type' => 'int']);
    }
}
