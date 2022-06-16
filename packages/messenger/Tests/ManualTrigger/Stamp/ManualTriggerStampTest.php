<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\Stamp;

use Draw\Component\Messenger\ManualTrigger\Stamp\ManualTriggerStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\ManualTrigger\Stamp\ManualTriggerStamp
 */
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
