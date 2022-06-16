<?php

namespace Draw\Component\Core\Tests\FilterExpression\Expression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Core\FilterExpression\Expression\ConstraintExpressionEvaluator
 */
class ConstraintExpressionEvaluatorTest extends TestCase
{
    private ConstraintExpressionEvaluator $object;

    public function setUp(): void
    {
        $this->object = new ConstraintExpressionEvaluator();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ExpressionEvaluator::class,
            $this->object
        );
    }

    public function testEvaluateInvalidExpression(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expression of class ['.CompositeExpression::class.'] is not supported');

        $this->object->evaluate(null, new CompositeExpression(CompositeExpression::TYPE_AND, []));
    }
}
