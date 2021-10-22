<?php

namespace Draw\Component\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class ExpirationStamp implements StampInterface
{
    /**
     * @var \DateTimeImmutable
     */
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTimeImmutable)
    {
        $this->dateTime = \DateTimeImmutable::createFromFormat('U', $dateTimeImmutable->getTimestamp());
    }

    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }
}
