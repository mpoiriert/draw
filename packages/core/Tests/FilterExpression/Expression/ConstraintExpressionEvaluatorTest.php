<?php

namespace Draw\Component\Core\Tests\FilterExpression\Expression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConstraintExpressionEvaluator::class)]
class ConstraintExpressionEvaluatorTest extends TestCase
{
    private ConstraintExpressionEvaluator $object;

    protected function setUp(): void
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expression of class ['.CompositeExpression::class.'] is not supported');

        $this->object->evaluate(null, new CompositeExpression(CompositeExpression::TYPE_AND, []));
    }
}
