<?php

namespace Draw\Component\Core\FilterExpression\Expression;

class CompositeExpression extends Expression
{
    const TYPE_AND = 'AND';

    const TYPE_OR = 'OR';

    /**
     * @var string
     */
    private $type;

    /**
     * @var array|Expression[]
     */
    private $expressions = [];

    public function __construct($type, $expressions)
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