<?php

namespace Draw\Component\OpenApi\Tests\Exception;

use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @covers \Draw\Component\OpenApi\Exception\ConstraintViolationListException
 */
class ConstraintViolationListExceptionTest extends TestCase
{
    private ConstraintViolationListException $object;

    private ConstraintViolationList $constraintViolationList;

    public function setUp(): void
    {
        $this->object = new ConstraintViolationListException(
            $this->constraintViolationList = new ConstraintViolationList()
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ValidatorException::class,
            $this->object
        );
    }

    public function testGetViolationList(): void
    {
        static::assertSame(
            $this->constraintViolationList,
            $this->object->getViolationList()
        );
    }
}
