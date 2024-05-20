<?php

declare(strict_types=1);

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronJobIntegration;
use Draw\Component\CronJob\Command\QueueCronJobByNameCommand;
use Draw\Component\CronJob\Command\QueueDueCronJobsCommand;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\EventListener\PostExecutionQueueCronJobListener;
use Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CronJobIntegration::class)]
class CronJobIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new CronJobIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'cron_job';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.cron_job.command.queue_cron_job_by_name_command',
                    [
                        QueueCronJobByNameCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.cron_job.command.queue_due_cron_jobs_command',
                    [
                        QueueDueCronJobsCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.cron_job.event_listener.post_execution_queue_cron_job_listener',
                    [
                        PostExecutionQueueCronJobListener::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.cron_job.cron_job_processor',
                    [
                        CronJobProcessor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.cron_job.message_handler.execute_cron_job_message_handler',
                    [
                        ExecuteCronJobMessageHandler::class,
                    ]
                ),
            ],
            [],
        ];
    }
}
