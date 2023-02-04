<?php

namespace Draw\Component\Core\FilterExpression;

use Draw\Component\Core\FilterExpression\Expression\Expression;

class Query
{
    private ?Expression $expression = null;

    public function where(Expression $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function andWhere(Expression $expression): self
    {
        if (null === $this->expression) {
            return $this->where($expression);
        }

        $this->expression = Expression::andX($this->expression, $expression);

        return $this;
    }

    public function orWhere(Expression $expression): self
    {
        if (null === $this->expression) {
            return $this->where($expression);
        }

        $this->expression = Expression::orX($this->expression, $expression);

        return $this;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }
}
