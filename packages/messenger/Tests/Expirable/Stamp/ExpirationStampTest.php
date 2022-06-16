<?php

namespace Draw\Component\Messenger\Tests\Expirable\Stamp;

use DateTimeImmutable;
use DateTimeInterface;
use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp
 */
class ExpirationStampTest extends TestCase
{
    private ExpirationStamp $entity;

    private DateTimeInterface $expiration;

    protected function setUp(): void
    {
        $this->entity = new ExpirationStamp(
            $this->expiration = new DateTimeImmutable()
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            StampInterface::class,
            $this->entity
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
