<?php

namespace Draw\Component\OpenApi\Tests\Exception;

use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @internal
 */
#[CoversClass(ConstraintViolationListException::class)]
class ConstraintViolationListExceptionTest extends TestCase
{
    private ConstraintViolationListException $object;

    private ConstraintViolationList $constraintViolationList;

    protected function setUp(): void
    {
        $this->object = new ConstraintViolationListException(
            $this->constraintViolationList = new ConstraintViolationList()
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
