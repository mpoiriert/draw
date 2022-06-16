<?php

namespace Draw\Component\Core\Tests\FilterExpression;

use Draw\Component\Core\FilterExpression\Evaluator;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpression;
use Draw\Component\Core\FilterExpression\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\EqualTo;

class EvaluatorTest extends TestCase
{
    private const SAMPLE_DATA = [
        ['property1' => 'value1'],
        ['property1' => 'value2'],
        ['property1' => 'value3', 'property2' => 'value4'],
    ];

    private $evaluator;

    public function setUp(): void
    {
        $this->evaluator = new Evaluator();
    }

    public function provideExecute(): iterable
    {
        yield 'simple-match' => [
            (new Query())->where(new ConstraintExpression('[property1]', new EqualTo('value1'))),
            1,
        ];

        yield 'no-match' => [
            (new Query())->where(new ConstraintExpression('[property1]', new EqualTo('value5'))),
            0,
        ];

        yield 'does-not-exists' => [
            (new Query())->where(new ConstraintExpression('[doesNotExists]', new EqualTo('value5'))),
            0,
        ];

        yield 'or-match' => [
            (new Query())
                ->where(new ConstraintExpression('[property1]', new EqualTo('value1')))
                ->orWhere(new ConstraintExpression('[property2]', new EqualTo('value4'))),
            2,
        ];

        yield 'or-no-match' => [
            (new Query())
                ->where(new ConstraintExpression('[property1]', new EqualTo('value-1')))
                ->orWhere(new ConstraintExpression('[property2]', new EqualTo('value-1'))),
            0,
        ];

        yield 'and-match' => [
            (new Query())
                ->where(new ConstraintExpression('[property1]', new EqualTo('value3')))
                ->andWhere(new ConstraintExpression('[property2]', new EqualTo('value4'))),
            1,
        ];

        yield 'and-no-match' => [
            (new Query())
                ->where(new ConstraintExpression('[property1]', new EqualTo('value1')))
                ->andWhere(new ConstraintExpression('[property2]', new EqualTo('value4'))),
            0,
        ];
    }

    /**
     * @dataProvider provideExecute
     */
    public function testExecute(Query $query, int $expectedCount)
    {
        static::assertCount(
            $expectedCount,
            $this->evaluator->execute(
                $query,
                static::SAMPLE_DATA
            )
        );
    }
}
