<?php

namespace Draw\Component\Messenger\Tests\Stamp;

use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Stamp\ManualTriggerStamp
 */
class ManualTriggerStampTest extends TestCase
{
    private ManualTriggerStamp $entity;

    public function setUp(): void
    {
        $this->entity = new ManualTriggerStamp();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            StampInterface::class,
            $this->entity
        );
    }
}
