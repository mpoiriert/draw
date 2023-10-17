<?php

namespace Draw\Component\Core\Tests\FilterExpression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\Core\FilterExpression\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
    private Query $object;

    protected function setUp(): void
    {
        $this->object = new Query();
    }

    public function testWhere(): void
    {
        static::assertSame(
            $this->object,
            $this->object->where($expression = $this->createMock(Expression::class))
        );

        static::assertSame(
            $expression,
            $this->object->getExpression()
        );
    }

    public function testAndWhereNoExpressionSet(): void
    {
        static::assertSame(
            $this->object,
            $this->object->andWhere($expression = $this->createMock(Expression::class))
        );

        static::assertSame(
            $expression,
            $this->object->getExpression()
        );
    }

    public function testAndWhere(): void
    {
        $this->object->andWhere($expression1 = $this->createMock(Expression::class));
        $this->object->andWhere($expression2 = $this->createMock(Expression::class));

        /** @var CompositeExpression $expression */
        $expression = $this->object->getExpression();

        static::assertInstanceOf(
            CompositeExpression::class,
            $expression,
        );

        static::assertSame(
            CompositeExpression::TYPE_AND,
            $expression->getType()
        );

        static::assertSame(
            [$expression1, $expression2],
            $expression->getExpressions()
        );
    }

    public function testOrWhereNoExpressionSet(): void
    {
        static::assertSame(
            $this->object,
            $this->object->orWhere($expression = $this->createMock(Expression::class))
        );

        static::assertSame(
            $expression,
            $this->object->getExpression()
        );
    }

    public function testOrWhere(): void
    {
        $this->object->orWhere($expression1 = $this->createMock(Expression::class));
        $this->object->orWhere($expression2 = $this->createMock(Expression::class));

        $expression = $this->object->getExpression();

        static::assertInstanceOf(
            CompositeExpression::class,
            $expression,
        );

        static::assertSame(
            CompositeExpression::TYPE_OR,
            $expression->getType()
        );

        static::assertSame(
            [$expression1, $expression2],
            $expression->getExpressions()
        );
    }
}
