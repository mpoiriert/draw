<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\Controller\ExecutionController;
use Draw\Bundle\SonataIntegrationBundle\CronJob\Controller\CronJobController;
use Draw\Bundle\SonataIntegrationBundle\CronJob\Controller\CronJobExecutionController;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\Configuration;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Controller\MessageController;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\EntityMigrator\Entity\Migration;
use Draw\Component\Tester\Test\DependencyInjection\ConfigurationTestCase;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
class ConfigurationTest extends ConfigurationTestCase
{
    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'configuration' => [
                'enabled' => true,
                'admin' => [
                    'entity_class' => Config::class,
                    'group' => 'draw.sonata.group.application',
                    'controller_class' => 'sonata.admin.controller.crud',
                    'icon' => 'fa fa-server',
                    'label' => 'config',
                    'pager_type' => 'default',
                    'show_in_dashboard' => true,
                    'translation_domain' => 'SonataAdminBundle',
                ],
            ],
            'console' => [
                'enabled' => true,
                'admin' => [
                    'group' => 'Command',
                    'entity_class' => Execution::class,
                    'controller_class' => ExecutionController::class,
                    'icon' => 'fas fa-terminal',
                    'label' => 'Execution',
                    'pager_type' => 'simple',
                    'show_in_dashboard' => true,
                    'translation_domain' => 'SonataAdminBundle',
                ],
                'commands' => [],
            ],
            'cron_job' => [
                'enabled' => true,
                'admin' => [
                    'cron_job' => [
                        'group' => 'Cron Job',
                        'entity_class' => CronJob::class,
                        'controller_class' => CronJobController::class,
                        'icon' => 'fas fa-clock',
                        'label' => 'Cron Job',
                        'pager_type' => 'simple',
                        'show_in_dashboard' => true,
                        'translation_domain' => 'DrawCronJobAdmin',
                    ],
                    'cron_job_execution' => [
                        'group' => 'Cron Job',
                        'entity_class' => CronJobExecution::class,
                        'controller_class' => CronJobExecutionController::class,
                        'icon' => 'fas fa-clock',
                        'label' => 'Cron Job Execution',
                        'pager_type' => 'simple',
                        'show_in_dashboard' => true,
                        'translation_domain' => 'DrawCronJobAdmin',
                    ],
                ],
            ],
            'entity_migrator' => [
                'enabled' => false,
                'admin' => [
                    'group' => 'Entity Migrator',
                    'entity_class' => Migration::class,
                    'controller_class' => 'sonata.admin.controller.crud',
                    'icon' => 'fa fa-cogs',
                    'label' => 'Migration',
                    'pager_type' => 'default',
                    'show_in_dashboard' => true,
                    'translation_domain' => 'DrawEntityMigratorAdmin',
                ],
            ],
            'messenger' => [
                'enabled' => true,
                'queue_names' => [],
                'monitoring_block' => [
                    'enabled' => true,
                ],
                'admin' => [
                    'group' => 'Messenger',
                    'entity_class' => MessengerMessage::class,
                    'controller_class' => MessageController::class,
                    'icon' => 'fas fa-rss',
                    'label' => 'Message',
                    'pager_type' => 'simple',
                    'show_in_dashboard' => true,
                    'translation_domain' => 'DrawMessengerAdmin',
                    'enabled' => true,
                ],
            ],
            'user' => [
                'enabled' => true,
                'user_admin_code' => UserAdmin::class,
                '2fa' => [
                    'enabled' => false,
                    'email' => [
                        'enabled' => false,
                    ],
                    'field_positions' => [
                        TwoFactorAuthenticationExtension::FIELD_2FA_ENABLED => [
                            'list' => ListMapper::NAME_ACTIONS,
                            'form' => true,
                        ],
                    ],
                ],
                'user_lock' => [
                    'enabled' => true,
                    'refresh_user_lock_extension' => [
                        'enabled' => true,
                    ],
                    'unlock_user_lock_extension' => [
                        'enabled' => true,
                    ],
                    'admin' => [
                        'group' => 'User',
                        'entity_class' => UserLock::class,
                        'controller_class' => 'sonata.admin.controller.crud',
                        'icon' => 'fas fa-ba',
                        'label' => 'User lock',
                        'pager_type' => 'simple',
                        'show_in_dashboard' => true,
                        'translation_domain' => 'SonataAdminBundle',
                    ],
                ],
            ],
        ];
    }

    public static function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['messenger' => ['queue_names' => 'test']],
            'Invalid type for path "draw_sonata_integration.messenger.queue_names". Expected array, but got string',
        ];

        yield [
            ['user' => ['user_admin_code' => []]],
            'Invalid type for path "draw_sonata_integration.user.user_admin_code". Expected scalar, but got array.',
        ];
    }
}
