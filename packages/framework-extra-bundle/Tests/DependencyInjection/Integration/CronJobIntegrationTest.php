<?php

declare(strict_types=1);

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronJobIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler;
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
                )
            ],
            [],
        ];
    }
}
