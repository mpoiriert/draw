<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use Draw\Component\Validator\Constraints\Strtotime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @internal
 */
#[CoversClass(Strtotime::class)]
class StrtotimeTest extends TestCase
{
    private Strtotime $object;

    protected function setUp(): void
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
        /** @var Type $constraint */
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
