<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use Draw\Component\Validator\Constraints\Strtotime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @covers \Draw\Component\Validator\Constraints\Strtotime
 */
class StrtotimeTest extends TestCase
{
    private Strtotime $object;

    public function setUp(): void
    {
        $this->object = new Strtotime();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(PhpCallable::class, $this->object);
    }

    public function testCallable(): void
    {
        static::assertSame(
            'strtotime',
            $this->object->callable
        );
    }

    public function testReturnValueConstraint(): void
    {
        $constraint = $this->object->returnValueConstraint;

        static::assertInstanceOf(
            Type::class,
            $constraint
        );

        static::assertSame(
            'int',
            $constraint->type
        );
    }
}
