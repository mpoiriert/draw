<?php

namespace Draw\Component\Messenger\Tests\Searchable\Stamp;

use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @internal
 */
#[CoversClass(FoundFromTransportStamp::class)]
class FindFromTransportStampTest extends TestCase
{
    private FoundFromTransportStamp $entity;

    private string $transportName;

    protected function setUp(): void
    {
        $this->entity = new FoundFromTransportStamp(
            $this->transportName = uniqid('transport-')
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            StampInterface::class,
            $this->entity
        );
    }

    public function testGetTransportName(): void
    {
        static::assertSame(
            $this->transportName,
            $this->entity->getTransportName()
        );
    }
}
