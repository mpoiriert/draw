<?php

namespace Draw\Component\Core;

final class DateTimeUtils
{
    private function __construct()
    {
    }

    public static function isSameTimestamp(?\DateTimeInterface $dateTime1, ?\DateTimeInterface $dateTime2): bool
    {
        switch (true) {
            case $dateTime1 === $dateTime2:
                return true;
            case null === $dateTime1:
            case null === $dateTime2:
                return false;
            default:
                return $dateTime1->getTimestamp() === $dateTime2->getTimestamp();
        }
    }

    public static function toDateTimeImmutable(?\DateTimeInterface $dateTime): ?\DateTimeImmutable
    {
        switch (true) {
            case null === $dateTime:
                return null;
            default:
                return \DateTimeImmutable::createFromFormat('U', (string) $dateTime->getTimestamp());
        }
    }

    public static function toDateTime(?\DateTimeInterface $dateTime): ?\DateTime
    {
        switch (true) {
            case null === $dateTime:
                return null;
            default:
                return \DateTime::createFromFormat('U', (string) $dateTime->getTimestamp());
        }
    }

    public static function millisecondDiff(\DateTimeInterface $dateTime, ?\DateTimeInterface $compareTo = null): int
    {
        if (null === $compareTo) {
            $compareTo = new \DateTimeImmutable();
        }

        return ($dateTime->getTimestamp() - $compareTo->getTimestamp()) * 1000;
    }
}
