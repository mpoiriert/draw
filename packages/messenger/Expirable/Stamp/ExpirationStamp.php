<?php

namespace Draw\Component\Messenger\Expirable\Stamp;

use Draw\Contracts\Messenger\EnvelopeFilterInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\StampInterface;

class ExpirationStamp implements StampInterface
{
    private \DateTimeImmutable $dateTime;

    public function __construct(\DateTimeInterface $expiration)
    {
        $this->dateTime = \DateTimeImmutable::createFromFormat('U', $expiration->getTimestamp());
    }

    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }

    public function isExpired(): bool
    {
        return $this->dateTime->getTimestamp() <= time();
    }

    public static function createEnvelopeFilter(): EnvelopeFilterInterface
    {
        return new class() implements EnvelopeFilterInterface {
            public function __invoke(Envelope $envelope): bool
            {
                if (null === $stamp = $envelope->last(ExpirationStamp::class)) {
                    return true;
                }

                return !$stamp->isExpired();
            }
        };
    }
}
