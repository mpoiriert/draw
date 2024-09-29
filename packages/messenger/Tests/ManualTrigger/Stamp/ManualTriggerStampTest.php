<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\Stamp;

use Draw\Component\Messenger\ManualTrigger\Stamp\ManualTriggerStamp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @internal
 */
#[CoversClass(ManualTriggerStamp::class)]
class ManualTriggerStampTest extends TestCase
{
    private ManualTriggerStamp $entity;

    protected function setUp(): void
    {
        $this->entity = new ManualTriggerStamp();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            StampInterface::class,
            $this->entity
        );
    }
}
