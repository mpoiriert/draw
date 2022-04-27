<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\RemoteFileExists;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

/**
 * @covers \Draw\Component\Validator\Constraints\RemoteFileExists
 */
class RemoteFileExistsTest extends TestCase
{
    private RemoteFileExists $object;

    protected function setUp(): void
    {
        $this->object = new RemoteFileExists();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Constraint::class,
            $this->object
        );
    }

    public function testGetTargets(): void
    {
        $this->assertSame(
            $this->object::PROPERTY_CONSTRAINT,
            $this->object->getTargets()
        );
    }
}
