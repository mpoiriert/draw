<?php

namespace Draw\Component\Core\Tests\FilterExpression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpression;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpression;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\EqualTo;

class ExpressionTest extends TestCase
{
    public function testEqual(): void
    {
        $expression = Expression::validate(
            $path = '[property]',
            $constraint = new EqualTo('value'),
            $groups = ['Default']
        );

        $this->assertInstanceOf(ConstraintExpression::class, $expression);
        $this->assertSame($path, $expression->getPath());
        $this->assertSame($constraint, $expression->getConstraints());
        $this->assertSame($groups, $expression->getGroups());
    }

    public function testAndX(): void
    {
        $expression = Expression::andX(
            $expression1 = Expression::validate('[property]'),
            $expression2 = Expression::validate('[property]')
        );

        $this->assertInstanceOf(CompositeExpression::class, $expression);
        $this->assertSame(CompositeExpression::TYPE_AND, $expression->getType());

        $expressions = $expression->getExpressions();

        $this->assertCount(2, $expressions);

        $this->assertSame($expression1, $expressions[0]);
        $this->assertSame($expression2, $expressions[1]);
    }

    public function testAndWhereEqual(): void
    {
        $expression = Expression::andWhereEqual([
            '[property1]' => 'value1',
            '[property2]' => 'value2',
        ]);

        $this->assertInstanceOf(CompositeExpression::class, $expression);
        $this->assertSame(CompositeExpression::TYPE_AND, $expression->getType());

        $expressions = $expression->getExpressions();

        $this->assertCount(2, $expressions);

        /** @var ConstraintExpression $expression */
        $expression = $expressions[0];
        $this->assertInstanceOf(ConstraintExpression::class, $expression);
        $this->assertSame('[property1]', $expression->getPath());
        $this->assertNull($expression->getGroups());
        /** @var EqualTo $constraint */
        $constraint = $expression->getConstraints();
        $this->assertInstanceOf(EqualTo::class, $constraint);
        $this->assertSame('value1', $constraint->value);

        /** @var ConstraintExpression $expression */
        $expression = $expressions[1];
        $this->assertInstanceOf(ConstraintExpression::class, $expression);
        $this->assertSame('[property2]', $expression->getPath());
        $this->assertNull($expression->getGroups());
        /** @var EqualTo $constraint */
        $constraint = $expression->getConstraints();
        $this->assertInstanceOf(EqualTo::class, $constraint);
        $this->assertSame('value2', $constraint->value);
    }

    public function testOrX(): void
    {
        $expression = Expression::orX(
            $expression1 = Expression::validate('[property]'),
            $expression2 = Expression::validate('[property]')
        );

        $this->assertInstanceOf(CompositeExpression::class, $expression);
        $this->assertSame(CompositeExpression::TYPE_OR, $expression->getType());

        $expressions = $expression->getExpressions();

        $this->assertCount(2, $expressions);

        $this->assertSame($expression1, $expressions[0]);
        $this->assertSame($expression2, $expressions[1]);
    }
}
