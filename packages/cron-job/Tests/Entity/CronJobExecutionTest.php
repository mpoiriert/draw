<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\Entity;

use Carbon\Carbon;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CronJobExecution::class)]
class CronJobExecutionTest extends TestCase
{
    #[DataProvider('provideDataForTestIsExecutable')]
    public function testIsExecutable(
        bool $expectedExecutable,
        bool $active,
        int $timeToLive = 0,
        ?\DateTimeImmutable $requestedAt = null,
        \DateTimeImmutable $now = new \DateTimeImmutable()
    ): void {
        Carbon::setTestNow($now);

        $execution = (new CronJobExecution())
            ->setRequestedAt($requestedAt)
            ->setCronJob(
                (new CronJob())
                    ->setActive($active)
                    ->setTimeToLive($timeToLive)
            );

        static::assertSame($expectedExecutable, $execution->isExecutable(Carbon::now()->toDateTimeImmutable()));
    }

    public static function provideDataForTestIsExecutable(): iterable
    {
        yield 'inactive' => [
            '$expectedExecutable' => false,
            '$active' => false,
        ];

        yield 'active with no time to live' => [
            '$expectedExecutable' => true,
            '$active' => true,
            '$timeToLive' => 0,
            '$requestedAt' => new \DateTimeImmutable('2024-04-17 00:00:00'),
            '$now' => new \DateTimeImmutable('2024-04-17 01:00:00'),
        ];

        yield 'inactive with no time to live' => [
            '$expectedExecutable' => false,
            '$active' => false,
            '$timeToLive' => 0,
            '$requestedAt' => new \DateTimeImmutable('2024-04-17 00:00:00'),
            '$now' => new \DateTimeImmutable('2024-04-17 01:00:00'),
        ];

        yield 'active with exceeding time to live' => [
            '$expectedExecutable' => false,
            '$active' => true,
            '$timeToLive' => 55,
            '$requestedAt' => new \DateTimeImmutable('2024-04-17 00:00:00'),
            '$now' => new \DateTimeImmutable('2024-04-17 00:00:59'),
        ];

        yield 'inactive with exceeding time to live' => [
            '$expectedExecutable' => false,
            '$active' => false,
            '$timeToLive' => 120,
            '$requestedAt' => new \DateTimeImmutable('2024-04-17 00:00:00'),
            '$now' => new \DateTimeImmutable('2024-04-17 00:00:59'),
        ];

        yield 'active with time to live' => [
            '$expectedExecutable' => true,
            '$active' => true,
            '$timeToLive' => 180,
            '$requestedAt' => new \DateTimeImmutable('2024-04-17 00:00:00'),
            '$now' => new \DateTimeImmutable('2024-04-17 00:02:59'),
        ];
    }
}
