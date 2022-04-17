<?php

namespace Draw\Component\Messenger\Tests\Stamp;

use Draw\Component\Messenger\Stamp\FindFromTransportStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Stamp\FindFromTransportStamp
 */
class FindFromTransportStampTest extends TestCase
{
    private FindFromTransportStamp $entity;

    private string $transportName;

    public function setUp(): void
    {
        $this->entity = new FindFromTransportStamp(
            $this->transportName = uniqid('transport-')
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            StampInterface::class,
            $this->entity
        );
    }

    public function testGetTransportName(): void
    {
        $this->assertSame(
            $this->transportName,
            $this->entity->getTransportName()
        );
    }
}
