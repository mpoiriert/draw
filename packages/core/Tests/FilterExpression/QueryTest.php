<?php

namespace Draw\Component\Core\Tests\FilterExpression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\Core\FilterExpression\Query;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Core\FilterExpression\Query
 */
class QueryTest extends TestCase
{
    private Query $object;

    public function setUp(): void
    {
        $this->object = new Query();
    }

    public function testWhere(): void
    {
        $this->assertSame(
            $this->object,
            $this->object->where($expression = $this->createMock(Expression::class))
        );

        $this->assertSame(
            $expression,
            $this->object->getExpression()
        );
    }

    public function testAndWhereNoExpressionSet(): void
    {
        $this->assertSame(
            $this->object,
            $this->object->andWhere($expression = $this->createMock(Expression::class))
        );

        $this->assertSame(
            $expression,
            $this->object->getExpression()
        );
    }

    public function testAndWhere(): void
    {
        $this->object->andWhere($expression1 = $this->createMock(Expression::class));
        $this->object->andWhere($expression2 = $this->createMock(Expression::class));

        $expression = $this->object->getExpression();

        $this->assertInstanceOf(
            CompositeExpression::class,
            $expression,
        );

        $this->assertSame(
            CompositeExpression::TYPE_AND,
            $expression->getType()
        );

        $this->assertSame(
            [$expression1, $expression2],
            $expression->getExpressions()
        );
    }

    public function testOrWhereNoExpressionSet(): void
    {
        $this->assertSame(
            $this->object,
            $this->object->orWhere($expression = $this->createMock(Expression::class))
        );

        $this->assertSame(
            $expression,
            $this->object->getExpression()
        );
    }

    public function testOrWhere(): void
    {
        $this->object->orWhere($expression1 = $this->createMock(Expression::class));
        $this->object->orWhere($expression2 = $this->createMock(Expression::class));

        $expression = $this->object->getExpression();

        $this->assertInstanceOf(
            CompositeExpression::class,
            $expression,
        );

        $this->assertSame(
            CompositeExpression::TYPE_OR,
            $expression->getType()
        );

        $this->assertSame(
            [$expression1, $expression2],
            $expression->getExpressions()
        );
    }
}
