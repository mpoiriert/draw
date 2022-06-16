<?php

namespace Draw\Component\Core\FilterExpression\Expression;

use Symfony\Component\Validator\Constraints\EqualTo;

abstract class Expression
{
    public static function orX(): CompositeExpression
    {
        return new CompositeExpression(CompositeExpression::TYPE_OR, \func_get_args());
    }

    public static function andX(): CompositeExpression
    {
        return new CompositeExpression(CompositeExpression::TYPE_AND, \func_get_args());
    }

    public static function validate($path, $constraints = null, $groups = null): ConstraintExpression
    {
        return new ConstraintExpression($path, $constraints, $groups);
    }

    /**
     * Helper method to common and where equal expression.
     */
    public static function andWhereEqual(array $propertyPathValueMap): CompositeExpression
    {
        $expressions = [];
        foreach ($propertyPathValueMap as $path => $value) {
            $expressions[] = static::validate($path, new EqualTo($value));
        }

        return \call_user_func_array([self::class, 'andX'], $expressions);
    }

    public function evaluateBy(): string
    {
        return static::class.'Evaluator';
    }
}
