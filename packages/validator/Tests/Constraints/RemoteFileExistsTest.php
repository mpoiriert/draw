<?php

namespace Draw\Component\Validator\Tests\Constraints;

use Draw\Component\Validator\Constraints\RemoteFileExists;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RemoteFileExists::class)]
class RemoteFileExistsTest extends TestCase
{
    private RemoteFileExists $object;

    protected function setUp(): void
    {
        $this->object = new RemoteFileExists();
    }

    public function testGetTargets(): void
    {
        static::assertSame(
            $this->object::PROPERTY_CONSTRAINT,
            $this->object->getTargets()
        );
    }
}
