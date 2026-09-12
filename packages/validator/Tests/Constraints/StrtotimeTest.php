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
        $this->assertInstanceOf(PhpCallable::class, $this->object);
    }

    public function testCallable(): void
    {
        $this->assertSame(
            'strtotime',
            $this->object->callable
        );
    }

    public function testDefaultMessage(): void
    {
        $this->assertSame(
            'The value {{ value }} is not valid to use in strtotime.',
            $this->object->message
        );
    }

    public function testMessageCanBeOverridden(): void
    {
        $this->assertSame(
            'Custom message.',
            (new Strtotime(message: 'Custom message.'))->message
        );
    }

    public function testReturnValueConstraint(): void
    {
        /** @var Type $constraint */
        $constraint = $this->object->returnValueConstraint;

        $this->assertInstanceOf(
            Type::class,
            $constraint
        );

        $this->assertSame(
            'int',
            $constraint->type
        );
    }
}
