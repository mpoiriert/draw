<?php

namespace Draw\Component\Core\Tests\FilterExpression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpression;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\EqualTo;

/**
 * @internal
 */
class ExpressionTest extends TestCase
{
    public function testEqual(): void
    {
        $expression = Expression::validate(
            $path = '[property]',
            $constraint = new EqualTo('value'),
            $groups = ['Default']
        );

        static::assertInstanceOf(ConstraintExpression::class, $expression);
        static::assertSame($path, $expression->getPath());
        static::assertSame($constraint, $expression->getConstraints());
        static::assertSame($groups, $expression->getGroups());
    }

    public function testAndX(): void
    {
        $expression = Expression::andX(
            $expression1 = Expression::validate('[property]'),
            $expression2 = Expression::validate('[property]')
        );

        static::assertInstanceOf(CompositeExpression::class, $expression);
        static::assertSame(CompositeExpression::TYPE_AND, $expression->getType());

        $expressions = $expression->getExpressions();

        static::assertCount(2, $expressions);

        static::assertSame($expression1, $expressions[0]);
        static::assertSame($expression2, $expressions[1]);
    }

    public function testAndWhereEqual(): void
    {
        $expression = Expression::andWhereEqual([
            '[property1]' => 'value1',
            '[property2]' => 'value2',
        ]);

        static::assertInstanceOf(CompositeExpression::class, $expression);
        static::assertSame(CompositeExpression::TYPE_AND, $expression->getType());

        $expressions = $expression->getExpressions();

        static::assertCount(2, $expressions);

        /** @var ConstraintExpression $expression */
        $expression = $expressions[0];
        static::assertInstanceOf(ConstraintExpression::class, $expression);
        static::assertSame('[property1]', $expression->getPath());
        static::assertNull($expression->getGroups());
        /** @var EqualTo $constraint */
        $constraint = $expression->getConstraints();
        static::assertInstanceOf(EqualTo::class, $constraint);
        static::assertSame('value1', $constraint->value);

        /** @var ConstraintExpression $expression */
        $expression = $expressions[1];
        static::assertInstanceOf(ConstraintExpression::class, $expression);
        static::assertSame('[property2]', $expression->getPath());
        static::assertNull($expression->getGroups());
        /** @var EqualTo $constraint */
        $constraint = $expression->getConstraints();
        static::assertInstanceOf(EqualTo::class, $constraint);
        static::assertSame('value2', $constraint->value);
    }

    public function testOrX(): void
    {
        $expression = Expression::orX(
            $expression1 = Expression::validate('[property]'),
            $expression2 = Expression::validate('[property]')
        );

        static::assertInstanceOf(CompositeExpression::class, $expression);
        static::assertSame(CompositeExpression::TYPE_OR, $expression->getType());

        $expressions = $expression->getExpressions();

        static::assertCount(2, $expressions);

        static::assertSame($expression1, $expressions[0]);
        static::assertSame($expression2, $expressions[1]);
    }
}
