<?php

namespace Draw\Component\Core\FilterExpression\Expression;

use Draw\Component\Core\FilterExpression\Evaluator;
use RuntimeException;

class CompositeExpressionEvaluator extends ExpressionEvaluator
{
    private $evaluator;

    public function __construct(Evaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    public function evaluate($data, Expression $expression): bool
    {
        if (!$expression instanceof CompositeExpression) {
            throw new RuntimeException('Expression of class ['.get_class($expression).'] is not supported');
        }

        $type = $expression->getType();
        if (!count($expressions = $expression->getExpressions())) {
            return true;
        }

        foreach ($expressions as $subExpression) {
            $result = $this->evaluator->evaluate($data, $subExpression);
            if ($result && CompositeExpression::TYPE_OR === $type) {
                return true;
            }

            if (!$result && CompositeExpression::TYPE_AND === $type) {
                return false;
            }
        }

        switch ($type) {
            case CompositeExpression::TYPE_AND:
                return true;
            case CompositeExpression::TYPE_OR:
                return false;
            default:
                throw new RuntimeException('Unsupported CompositeExpression type ['.$type.']');
        }
    }
}
