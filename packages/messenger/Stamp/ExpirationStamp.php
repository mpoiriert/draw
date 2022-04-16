<?php

namespace Draw\Component\Messenger\Stamp;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

class ExpirationStamp implements StampInterface
{
    private DateTimeImmutable $dateTime;

    public function __construct(DateTimeInterface $expiration)
    {
        $this->dateTime = DateTimeImmutable::createFromFormat('U', $expiration->getTimestamp());
    }

    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }
}
