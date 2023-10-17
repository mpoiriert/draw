<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use Draw\Component\Validator\Constraints\PhpCallableValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

#[CoversClass(PhpCallable::class)]
class PhpCallableTest extends TestCase
{
    private PhpCallable $object;

    protected function setUp(): void
    {
        $this->object = new PhpCallable('strtotime');
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Constraint::class,
            $this->object
        );

        static::assertSame(
            'Execution of function with {{ value }} does not return expected result.',
            $this->object->message
        );

        static::assertSame(
            'strtotime',
            $this->object->callable
        );

        static::assertTrue($this->object->ignoreNull);

        static::assertNull($this->object->returnValueConstraint);
    }

    public function testGetDefaultOption(): void
    {
        static::assertSame(
            'callable',
            $this->object->getDefaultOption()
        );
    }

    public function testGetRequiredOptions(): void
    {
        static::assertSame(
            ['callable'],
            $this->object->getRequiredOptions()
        );
    }

    public function testValidatedBy(): void
    {
        static::assertSame(
            PhpCallableValidator::class,
            $this->object->validatedBy()
        );
    }
}
