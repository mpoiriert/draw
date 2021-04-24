<?php

namespace Draw\Component\Core\FilterExpression\Expression;

abstract class ExpressionEvaluator
{
    abstract public function evaluate($data, Expression $expression): bool;
}