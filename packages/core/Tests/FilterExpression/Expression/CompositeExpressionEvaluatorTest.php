<?php

namespace Draw\Component\Core\Tests\FilterExpression\Expression;

use Draw\Component\Core\FilterExpression\Evaluator;
use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\CompositeExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpression;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CompositeExpressionEvaluator::class)]
class CompositeExpressionEvaluatorTest extends TestCase
{
    public function testEvaluateInvalidExpression(): void
    {
        $object = new CompositeExpressionEvaluator(
            static::createStub(Evaluator::class)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expression of class ['.ConstraintExpression::class.'] is not supported');

        $object->evaluate(null, new ConstraintExpression(null));
    }

    public function testEvaluateNoExpression(): void
    {
        $object = new CompositeExpressionEvaluator(
            $evaluator = $this->createMock(Evaluator::class)
        );

        $evaluator
            ->expects(static::never())
            ->method('evaluate')
            ->seal()
        ;

        static::assertTrue(
            $object->evaluate(null, new CompositeExpression(CompositeExpression::TYPE_AND, []))
        );
    }

    public function testEvaluateInvalidType(): void
    {
        $object = new CompositeExpressionEvaluator(
            $evaluator = $this->createMock(Evaluator::class)
        );

        $type = uniqid('type');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported CompositeExpression type ['.$type.']');

        $evaluator
            ->expects(static::never())
            ->method('evaluate')
            ->seal()
        ;

        static::assertTrue(
            $object->evaluate(null, new CompositeExpression($type, []))
        );
    }
}
