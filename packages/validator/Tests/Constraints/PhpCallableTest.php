<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\PhpCallable;
use Draw\Component\Validator\Constraints\PhpCallableValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

/**
 * @covers \Draw\Component\Validator\Constraints\PhpCallable
 */
class PhpCallableTest extends TestCase
{
    private PhpCallable $object;

    protected function setUp(): void
    {
        $this->object = new PhpCallable('strtotime');
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Constraint::class,
            $this->object
        );

        $this->assertSame(
            'Execution of function with {{ value }} does not return expected result.',
            $this->object->message
        );

        $this->assertSame(
            'strtotime',
            $this->object->callable
        );

        $this->assertTrue($this->object->ignoreNull);

        $this->assertNull($this->object->returnValueConstraint);
    }

    public function testGetDefaultOption(): void
    {
        $this->assertSame(
            'callable',
            $this->object->getDefaultOption()
        );
    }

    public function testGetRequiredOptions(): void
    {
        $this->assertSame(
            ['callable'],
            $this->object->getRequiredOptions()
        );
    }

    public function testValidatedBy(): void
    {
        $this->assertSame(
            PhpCallableValidator::class,
            $this->object->validatedBy()
        );
    }
}
