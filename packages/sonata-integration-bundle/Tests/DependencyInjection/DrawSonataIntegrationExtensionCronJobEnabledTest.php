<?php

declare(strict_types=1);

namespace DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\CronJob\Admin\CronJobAdmin;
use Draw\Bundle\SonataIntegrationBundle\CronJob\Admin\CronJobExecutionAdmin;
use Draw\Bundle\SonataIntegrationBundle\CronJob\Controller\CronJobController;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection\DrawSonataIntegrationExtensionTest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DrawSonataIntegrationExtension::class)]
class DrawSonataIntegrationExtensionCronJobEnabledTest extends DrawSonataIntegrationExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['cron_job'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [CronJobAdmin::class];
        yield [CronJobExecutionAdmin::class];
        yield [CronJobController::class];
    }
}
