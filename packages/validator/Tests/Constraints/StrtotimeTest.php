<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use Draw\Component\Validator\Constraints\PhpCallableValidator;
use Draw\Component\Validator\Constraints\Strtotime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;

class StrtotimeTest extends TestCase
{
    /**
     * @var Strtotime
     */
    private $constraint;

    public function setUp(): void
    {
        $this->constraint = new Strtotime();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(PhpCallable::class, $this->constraint);
    }

    public function testValidateBy(): void
    {
        $this->assertSame(
            PhpCallableValidator::class,
            $this->constraint->validatedBy()
        );
    }

    public function testCallable(): void
    {
        $this->assertSame(
            'strtotime',
            $this->constraint->callable
        );
    }

    public function testReturnValueConstraint(): void
    {
        $this->assertInstanceOf(
            Type::class,
            $this->constraint->returnValueConstraint
        );

        $this->assertSame(
            'int',
            $this->constraint->returnValueConstraint->type
        );
    }
}
