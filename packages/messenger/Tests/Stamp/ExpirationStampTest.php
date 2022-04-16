<?php

namespace Draw\Component\Messenger\Tests\Stamp;

use DateTimeImmutable;
use DateTimeInterface;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Stamp\ExpirationStamp
 */
class ExpirationStampTest extends TestCase
{
    private ExpirationStamp $entity;

    private DateTimeInterface $expiration;

    public function setUp(): void
    {
        $this->entity = new ExpirationStamp(
            $this->expiration = new DateTimeImmutable()
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            StampInterface::class,
            $this->entity
        );
    }

    public function testGetDateTime(): void
    {
        $this->assertSame(
            $this->expiration->getTimestamp(),
            $this->entity->getDateTime()->getTimestamp()
        );
    }
}
