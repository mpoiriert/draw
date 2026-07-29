<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\Command;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\Command\QueueCronJobByNameCommand;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @internal
 */
#[CoversClass(QueueCronJobByNameCommand::class)]
#[AllowMockObjectsWithoutExpectations]
class QueueCronJobByNameCommandTest extends TestCase
{
    use CommandTestTrait;

    private ManagerRegistry&MockObject $managerRegistry;

    private CronJobProcessor&MockObject $cronJobProcessor;

    private EntityRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry
            ->expects($this->any())
            ->method('getRepository')
            ->with(CronJob::class)
            ->willReturn($this->repository = $this->createMock(EntityRepository::class))
        ;

        $this->command = new QueueCronJobByNameCommand(
            $this->managerRegistry,
            $this->cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );
    }

    public function getCommandName(): string
    {
        return 'draw:cron-job:queue-by-name';
    }

    public static function provideTestArgument(): iterable
    {
        yield ['name', InputArgument::REQUIRED];
    }

    public static function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecuteWithExistingCronJob(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => $cronJobName = 'Existing Cron Job'])
            ->willReturn($cronJob = new CronJob())
        ;

        $this->cronJobProcessor
            ->expects($this->once())
            ->method('queue')
            ->with($cronJob, true)
        ;

        $this
            ->execute(['name' => $cronJobName])
            ->test(
                CommandDataTester::create(
                    Command::SUCCESS,
                    [
                        'Queueing cron job...',
                        'Cron job successfully queued.',
                    ]
                )
            )
        ;
    }

    public function testExecuteWithoutExistingCronJob(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => $cronJobName = 'Invalid Cron Job'])
            ->willReturn(null)
        ;

        $this->cronJobProcessor
            ->expects($this->never())
            ->method('queue')
        ;

        $this
            ->execute(['name' => $cronJobName])
            ->test(
                CommandDataTester::create(
                    Command::FAILURE,
                    [
                        '[ERROR] Cron job could not be found.',
                    ]
                )
            )
        ;
    }
}
