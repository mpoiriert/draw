<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\Command;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\Command\QueueDueCronJobsCommand;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

#[CoversClass(QueueDueCronJobsCommand::class)]
class QueueDueCronJobsCommandTest extends TestCase
{
    use CommandTestTrait;
    use MockTrait;

    private ManagerRegistry&MockObject $managerRegistry;

    private CronJobProcessor&MockObject $cronJobProcessor;

    public function createCommand(): Command
    {
        return new QueueDueCronJobsCommand(
            $this->managerRegistry = $this->createMock(ManagerRegistry::class),
            $this->cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:cron-job:queue-due';
    }

    public static function provideTestArgument(): iterable
    {
        return [];
    }

    public static function provideTestOption(): iterable
    {
        return [];
    }

    /**
     * @param array{name: string, due: bool}[] $rawCronJobs
     * @param string[]                         $expectedDisplay
     */
    #[DataProvider('provideDataForTestExecute')]
    public function testExecute(array $rawCronJobs, array $expectedDisplay): void
    {
        $this->managerRegistry
            ->expects(static::any())
            ->method('getRepository')
            ->with(CronJob::class)
            ->willReturn($repository = $this->createMock(EntityRepository::class));

        $repository
            ->expects(static::once())
            ->method('findBy')
            ->with(['active' => true])
            ->willReturn(
                $cronJobs = array_map(
                    fn (array $rawCronJob): CronJob&MockObject => $this->createCronJob(
                        $rawCronJob['name'],
                        $rawCronJob['due']
                    ),
                    $rawCronJobs
                )
            );

        $dueCronJobs = array_filter(
            $cronJobs,
            static fn (CronJob $cronJob): bool => $cronJob->isDue()
        );

        if (0 === $numDueCronJobs = \count($dueCronJobs)) {
            $this->cronJobProcessor
                ->expects(static::never())
                ->method('queue');
        } else {
            $this->cronJobProcessor
                ->expects(static::exactly($numDueCronJobs))
                ->method('queue')
                ->with(
                    ...static::withConsecutive(...array_map(
                        static fn (CronJob $cronJob): array => [$cronJob, false],
                        $dueCronJobs
                    ))
                );
        }

        $this
            ->execute([])
            ->test(
                CommandDataTester::create(
                    Command::SUCCESS,
                    array_merge(
                        [
                            'Queueing cron jobs...',
                            '---------------------',
                        ],
                        $expectedDisplay,
                        [
                            '[OK] Cron jobs successfully queued...',
                        ]
                    )
                )
            );
    }

    public static function provideDataForTestExecute(): iterable
    {
        yield [
            'rawCronJobs' => [],
            'expectedDisplay' => [],
        ];

        yield [
            'rawCronJobs' => [
                ['name' => 'CronJob1', 'due' => true],
                ['name' => 'CronJob2', 'due' => false],
                ['name' => 'CronJob3', 'due' => true],
            ],
            'expectedDisplay' => [
                '! [NOTE] Queueing cron job "CronJob1"...',
                '! [NOTE] Queueing cron job "CronJob3"...',
            ],
        ];

        yield [
            'rawCronJobs' => [
                ['name' => 'CronJob1', 'due' => false],
                ['name' => 'CronJob2', 'due' => false],
                ['name' => 'CronJob3', 'due' => false],
                ['name' => 'CronJob4', 'due' => false],
            ],
            'expectedDisplay' => [],
        ];

        yield [
            'rawCronJobs' => [
                ['name' => 'CronJob1', 'due' => true],
                ['name' => 'CronJob2', 'due' => true],
                ['name' => 'CronJob3', 'due' => true],
                ['name' => 'CronJob4', 'due' => true],
                ['name' => 'CronJob5', 'due' => false],
            ],
            'expectedDisplay' => [
                '! [NOTE] Queueing cron job "CronJob1"...',
                '! [NOTE] Queueing cron job "CronJob2"...',
                '! [NOTE] Queueing cron job "CronJob3"...',
                '! [NOTE] Queueing cron job "CronJob4"...',
            ],
        ];
    }

    private function createCronJob(string $name, bool $due): CronJob&MockObject
    {
        $cronJob = $this->createMock(CronJob::class);
        $cronJob
            ->expects(static::any())
            ->method('getName')
            ->willReturn($name);
        $cronJob
            ->expects(static::any())
            ->method('isDue')
            ->willReturn($due);

        return $cronJob;
    }
}
