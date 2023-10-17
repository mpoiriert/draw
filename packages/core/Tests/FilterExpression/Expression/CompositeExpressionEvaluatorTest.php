<?php

namespace Draw\Component\Core\Tests\FilterExpression\Expression;

use Draw\Component\Core\FilterExpression\Evaluator;
use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\CompositeExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpression;
use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompositeExpressionEvaluator::class)]
class CompositeExpressionEvaluatorTest extends TestCase
{
    private CompositeExpressionEvaluator $object;

    private Evaluator&MockObject $evaluator;

    protected function setUp(): void
    {
        $this->object = new CompositeExpressionEvaluator(
            $this->evaluator = $this->createMock(Evaluator::class)
        );
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
        $this->expectExceptionMessage('Expression of class ['.ConstraintExpression::class.'] is not supported');

        $this->object->evaluate(null, new ConstraintExpression(null));
    }

    public function testEvaluateNoExpression(): void
    {
        $this->evaluator
            ->expects(static::never())
            ->method('evaluate');

        static::assertTrue(
            $this->object->evaluate(null, new CompositeExpression(CompositeExpression::TYPE_AND, []))
        );
    }

    public function testEvaluateInvalidType(): void
    {
        $type = uniqid('type');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported CompositeExpression type ['.$type.']');

        $this->evaluator
            ->expects(static::never())
            ->method('evaluate');

        static::assertTrue(
            $this->object->evaluate(null, new CompositeExpression($type, []))
        );
    }
}
