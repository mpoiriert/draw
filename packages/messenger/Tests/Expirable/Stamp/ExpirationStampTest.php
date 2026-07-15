<?php

namespace Draw\Component\Messenger\Tests\Expirable\Stamp;

use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExpirationStamp::class)]
class ExpirationStampTest extends TestCase
{
    private ExpirationStamp $entity;

    private \DateTimeInterface $expiration;

    protected function setUp(): void
    {
        $this->entity = new ExpirationStamp(
            $this->expiration = new \DateTimeImmutable()
        );
    }

    public function testGetDateTime(): void
    {
        static::assertSame(
            $this->expiration->getTimestamp(),
            $this->entity->getDateTime()->getTimestamp()
        );
    }
}
