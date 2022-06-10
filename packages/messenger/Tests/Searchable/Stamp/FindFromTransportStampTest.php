<?php

namespace Draw\Component\Messenger\Tests\Searchable\Stamp;

use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp
 */
class FindFromTransportStampTest extends TestCase
{
    private FoundFromTransportStamp $entity;

    private string $transportName;

    public function setUp(): void
    {
        $this->entity = new FoundFromTransportStamp(
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
