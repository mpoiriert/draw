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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @internal
 */
#[CoversClass(QueueCronJobByNameCommand::class)]
class QueueCronJobByNameCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        $this->command = new QueueCronJobByNameCommand(
            static::createStub(ManagerRegistry::class),
            static::createStub(CronJobProcessor::class)
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
        $this->command = new QueueCronJobByNameCommand(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            $cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );

        $managerRegistry
            ->expects(static::once())
            ->method('getRepository')
            ->with(CronJob::class)
            ->willReturn($repository = $this->createMock(EntityRepository::class))
            ->seal()
        ;

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['name' => $cronJobName = 'Existing Cron Job'])
            ->willReturn($cronJob = new CronJob())
            ->seal()
        ;

        $cronJobProcessor
            ->expects(static::once())
            ->method('queue')
            ->with($cronJob, true)
            ->seal()
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
        $this->command = new QueueCronJobByNameCommand(
            $managerRegistry = $this->createMock(ManagerRegistry::class),
            $cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );

        $managerRegistry
            ->expects(static::once())
            ->method('getRepository')
            ->with(CronJob::class)
            ->willReturn($repository = $this->createMock(EntityRepository::class))
            ->seal()
        ;

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['name' => $cronJobName = 'Invalid Cron Job'])
            ->willReturn(null)
            ->seal()
        ;

        $cronJobProcessor
            ->expects(static::never())
            ->method('queue')
            ->seal()
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
