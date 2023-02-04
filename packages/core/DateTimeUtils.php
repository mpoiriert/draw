<?php

namespace Draw\Component\Core;

final class DateTimeUtils
{
    private function __construct()
    {
    }

    public static function isSameTimestamp(?\DateTimeInterface $dateTime1, ?\DateTimeInterface $dateTime2): bool
    {
        return match (true) {
            $dateTime1 === $dateTime2 => true,
            null === $dateTime1, null === $dateTime2 => false,
            default => $dateTime1->getTimestamp() === $dateTime2->getTimestamp(),
        };
    }

    public static function toDateTimeImmutable(?\DateTimeInterface $dateTime): ?\DateTimeImmutable
    {
        return match (true) {
            null === $dateTime => null,
            default => \DateTimeImmutable::createFromFormat('U', (string) $dateTime->getTimestamp()),
        };
    }

    public static function toDateTime(?\DateTimeInterface $dateTime): ?\DateTime
    {
        return match (true) {
            null === $dateTime => null,
            default => \DateTime::createFromFormat('U', (string) $dateTime->getTimestamp()),
        };
    }

    public static function millisecondDiff(\DateTimeInterface $dateTime, ?\DateTimeInterface $compareTo = null): int
    {
        if (null === $compareTo) {
            $compareTo = new \DateTimeImmutable();
        }

        return ($dateTime->getTimestamp() - $compareTo->getTimestamp()) * 1000;
    }
}
