<?php

namespace Draw\Component\Core\FilterExpression\Expression;

class CompositeExpression extends Expression
{
    public const TYPE_AND = 'AND';

    public const TYPE_OR = 'OR';

    private string $type;

    /**
     * @var array|Expression[]
     */
    private array $expressions;

    public function __construct(string $type, array $expressions)
    {
        $this->type = $type;
        $this->expressions = $expressions;
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
