<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use Draw\Component\Validator\Constraints\PhpCallableValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @covers \Draw\Component\Validator\Constraints\PhpCallableValidator
 */
class PhpCallableValidatorTest extends TestCase
{
    private PhpCallableValidator $object;

    protected function setUp(): void
    {
        $this->object = new PhpCallableValidator();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ConstraintValidatorInterface::class,
            $this->object
        );
    }

    public function testValidateInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Expected argument of type "%s", "%s" given',
                PhpCallable::class,
                NotNull::class
            )
        );

        $this->object->validate(null, new NotNull());
    }

    /**
     * @return array<string, array{0: mixed, ?Constraint, int}>
     */
    public function provideTestValidate(): array
    {
        return [
            'execution-only' => [null, null, 0],
            'null' => [null, new IsTrue(), 0],
            'exception' => [new \Exception(), new IsTrue(), 1],
            'match-return-value-constraint' => [true, new IsTrue(), 0],
            'does-not-match-return-value-constraint' => [false, new IsTrue(), 1],
        ];
    }

    /**
     * @dataProvider provideTestValidate
     *
     * @param mixed $value
     */
    public function testValidate($value, ?Constraint $returnValueConstraint, int $violationsCount): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate(
            $value,
            [
                new PhpCallable([
                    'callable' => function ($value) {
                        if ($value instanceof \Exception) {
                            throw $value;
                        }

                        return $value;
                    },
                    'returnValueConstraint' => $returnValueConstraint,
                ]),
            ]
        );

        static::assertEquals($violationsCount, $violations->count());
    }
}
