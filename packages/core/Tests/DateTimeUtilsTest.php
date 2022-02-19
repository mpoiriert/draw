<?php

namespace Draw\Component\Core\Tests;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Draw\Component\Core\DateTimeUtils;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    public function provideTestIsSameTimestamp(): array
    {
        return [
            'both-null' => [
                null,
                null,
                true,
            ],
            'first-null' => [
                null,
                new DateTime(),
                false,
            ],
            'second-null' => [
                new DateTime(),
                null,
                false,
            ],
            'same-object' => [
                $dateTime = new DateTime(),
                $dateTime,
                true,
            ],
            'same-date-time' => [
                new DateTime('2000-01-01 00:00:00'),
                new DateTime('2000-01-01 00:00:00'),
                true,
            ],
            'same-date-time-immutable' => [
                new DateTimeImmutable('2000-01-01 00:00:00'),
                new DateTimeImmutable('2000-01-01 00:00:00'),
                true,
            ],
            'same-date-different-type' => [
                new DateTimeImmutable('2000-01-01 00:00:00'),
                new DateTime('2000-01-01 00:00:00'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideTestIsSameTimestamp
     */
    public function testIsSameTimestamp(
        ?DateTimeInterface $dateTime1,
        ?DateTimeInterface $dateTime2,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            DateTimeUtils::isSameTimestamp($dateTime1, $dateTime2)
        );
    }

    public function provideTestToDateTimeX(): array
    {
        return [
            'null' => [null],
            'date-time' => [new DateTime()],
            'date-time-immutable' => [new DateTimeImmutable()],
        ];
    }

    /**
     * @dataProvider provideTestToDateTimeX
     */
    public function testToDateTimeImmutable(?DateTimeInterface $dateTimeInterface): void
    {
        $dateTimeImmutable = DateTimeUtils::toDateTimeImmutable($dateTimeInterface);
        if (null === $dateTimeInterface) {
            $this->assertNull($dateTimeImmutable);

            return;
        }

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTimeImmutable);
        $this->assertTrue(DateTimeUtils::isSameTimestamp($dateTimeInterface, $dateTimeImmutable));
        $this->assertNotSame($dateTimeInterface, $dateTimeImmutable);
    }

    /**
     * @dataProvider provideTestToDateTimeX
     */
    public function testToDateTime(?DateTimeInterface $dateTimeInterface): void
    {
        $dateTime = DateTimeUtils::toDateTime($dateTimeInterface);
        if (null === $dateTimeInterface) {
            $this->assertNull($dateTime);

            return;
        }

        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertTrue(DateTimeUtils::isSameTimestamp($dateTimeInterface, $dateTime));
        $this->assertNotSame($dateTimeInterface, $dateTime);
    }
}
