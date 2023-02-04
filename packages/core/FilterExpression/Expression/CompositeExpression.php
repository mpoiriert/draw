<?php

namespace Draw\Component\Core\FilterExpression\Expression;

class CompositeExpression extends Expression
{
    final public const TYPE_AND = 'AND';

    final public const TYPE_OR = 'OR';

    /**
     * @param array|Expression[] $expressions
     */
    public function __construct(private string $type, private array $expressions)
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array|Expression[]
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }
}
