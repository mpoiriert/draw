<?php

namespace Draw\Component\Core\Tests;

use Draw\Component\Core\DateTimeUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    public static function provideTestIsSameTimestamp(): array
    {
        return [
            'both-null' => [
                null,
                null,
                true,
            ],
            'first-null' => [
                null,
                new \DateTime(),
                false,
            ],
            'second-null' => [
                new \DateTime(),
                null,
                false,
            ],
            'same-object' => [
                $dateTime = new \DateTime(),
                $dateTime,
                true,
            ],
            'same-date-time' => [
                new \DateTime('2000-01-01 00:00:00'),
                new \DateTime('2000-01-01 00:00:00'),
                true,
            ],
            'same-date-time-immutable' => [
                new \DateTimeImmutable('2000-01-01 00:00:00'),
                new \DateTimeImmutable('2000-01-01 00:00:00'),
                true,
            ],
            'same-date-different-type' => [
                new \DateTimeImmutable('2000-01-01 00:00:00'),
                new \DateTime('2000-01-01 00:00:00'),
                true,
            ],
        ];
    }

    #[DataProvider('provideTestIsSameTimestamp')]
    public function testIsSameTimestamp(
        ?\DateTimeInterface $dateTime1,
        ?\DateTimeInterface $dateTime2,
        bool $expected,
    ): void {
        static::assertSame(
            $expected,
            DateTimeUtils::isSameTimestamp($dateTime1, $dateTime2)
        );
    }

    public static function provideTestToDateTimeX(): array
    {
        return [
            'null' => [null],
            'date-time' => [new \DateTime()],
            'date-time-immutable' => [new \DateTimeImmutable()],
        ];
    }

    #[DataProvider('provideTestToDateTimeX')]
    public function testToDateTimeImmutable(?\DateTimeInterface $dateTimeInterface): void
    {
        $dateTimeImmutable = DateTimeUtils::toDateTimeImmutable($dateTimeInterface);
        if (null === $dateTimeInterface) {
            static::assertNull($dateTimeImmutable);

            return;
        }

        static::assertInstanceOf(\DateTimeImmutable::class, $dateTimeImmutable);
        static::assertTrue(DateTimeUtils::isSameTimestamp($dateTimeInterface, $dateTimeImmutable));
        static::assertNotSame($dateTimeInterface, $dateTimeImmutable);
    }

    #[DataProvider('provideTestToDateTimeX')]
    public function testToDateTime(?\DateTimeInterface $dateTimeInterface): void
    {
        $dateTime = DateTimeUtils::toDateTime($dateTimeInterface);
        if (null === $dateTimeInterface) {
            static::assertNull($dateTime);

            return;
        }

        static::assertInstanceOf(\DateTime::class, $dateTime);
        static::assertTrue(DateTimeUtils::isSameTimestamp($dateTimeInterface, $dateTime));
        static::assertNotSame($dateTimeInterface, $dateTime);
    }

    public static function provideTestMillisecondDiff(): array
    {
        return [
            'now compare' => ['now', null, 0],
            '5 seconds ago' => ['- 5 seconds', null, -5000],
            '5 seconds in the future' => ['+ 5 seconds', null, 5000],
            'specifying a compare to date' => ['+ 5 seconds', '+ 5 seconds', 0],
        ];
    }

    #[DataProvider('provideTestMillisecondDiff')]
    public function testMillisecondDiff(string $delay, ?string $compareToDelay, int $expected): void
    {
        static::assertSame(
            $expected,
            DateTimeUtils::millisecondDiff(
                new \DateTimeImmutable($delay),
                $compareToDelay ? new \DateTimeImmutable($compareToDelay) : null
            )
        );
    }
}
