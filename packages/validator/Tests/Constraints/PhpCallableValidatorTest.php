<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Validation;

class PhpCallableValidatorTest extends TestCase
{
    public function provideTestValidate(): array
    {
        return [
            'no-error' => [true, new IsTrue(), 0],
            'with-error' => [false, new IsTrue(), 1],
        ];
    }

    /**
     * @dataProvider provideTestValidate
     */
    public function testValidate($value, Constraint $returnValueConstraint, int $violationsCount): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate(
            $value,
            [
                new PhpCallable([
                    'callable' => function ($value) {
                        return $value;
                    },
                    'returnValueConstraint' => $returnValueConstraint,
                ]),
            ]
        );

        $this->assertEquals($violationsCount, $violations->count());
    }
}
