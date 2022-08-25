<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\Controller\ExecutionController;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\Configuration;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                ],
                'commands' => [],
            ],
            'messenger' => [
                'enabled' => true,
                'queue_names' => [],
                'admin' => [
                    'group' => 'Messenger',
                    'entity_class' => MessengerMessage::class,
                    'controller_class' => 'sonata.admin.controller.crud',
                    'icon' => 'fas fa-rss',
                    'label' => 'Message',
                    'pager_type' => 'simple',
                    'show_in_dashboard' => true,
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
                    ],
                ],
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
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
