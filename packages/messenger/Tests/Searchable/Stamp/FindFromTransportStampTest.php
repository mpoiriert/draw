<?php

namespace Draw\Component\Messenger\Tests\Searchable\Stamp;

use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

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

    public function testGetTransportName(): void
    {
        static::assertSame(
            $this->transportName,
            $this->entity->getTransportName()
        );
    }
}
