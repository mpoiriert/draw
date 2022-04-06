<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\AccountLocker\Sonata\Controller\UserLockController;
use Draw\Bundle\UserBundle\DependencyInjection\Configuration;
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
            'account_locker' => [
                'enabled' => false,
                'account_locked_route' => 'draw_user_account_locker_account_locked',
                'entity' => [
                    'enabled' => false,
                ],
                'sonata' => [
                    'enabled' => false,
                    'model_class' => UserLock::class,
                    'controller' => UserLockController::class,
                    'group' => 'User',
                    'show_in_dashboard' => true,
                    'icon' => 'fas fa-ban',
                    'label' => 'User lock',
                    'pager_type' => 'simple',
                ],
            ],
            'email_writers' => [
                'enabled' => false,
            ],
            'password_recovery' => [
                'enabled' => false,
                'email' => [
                    'enabled' => false,
                ],
            ],
            'onboarding' => [
                'enabled' => false,
                'email' => [
                    'enabled' => false,
                    'expiration_delay' => '+ 7 days',
                ],
            ],
            'enforce_2fa' => [
                'enabled' => false,
                'enable_route' => 'admin_app_user_enable-2fa',
                'enforcing_roles' => [],
            ],
            'password_change_enforcer' => [
                'enabled' => false,
                'change_password_route' => 'admin_change_password',
                'email' => [
                    'enabled' => false,
                ],
            ],
            'sonata' => [
                'enabled' => true,
                'user_admin_code' => UserAdmin::class,
                '2fa' => [
                    'enabled' => false,
                    'field_positions' => [
                        '2fa_enabled' => [
                            'list' => defined(ListMapper::class.'NAME_ACTIONS') ? ListMapper::NAME_ACTIONS : '_action',
                            'form' => true,
                        ],
                    ],
                ],
            ],
            'encrypt_password_listener' => [
                'enabled' => true,
                'auto_generate_password' => true,
            ],
            'user_entity_class' => User::class,
            'reset_password_route' => 'admin_change_password',
            'invite_create_account_route' => 'home',
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['sonata' => ['user_admin_code' => []]],
            'Invalid type for path "draw_user.sonata.user_admin_code". Expected scalar, but got array.',
        ];

        yield [
            ['encrypt_password_listener' => 'string-not-supported'],
            'Invalid type for path "draw_user.encrypt_password_listener". Expected array, but got string',
        ];

        yield [
            ['user_entity_class' => []],
            'Invalid type for path "draw_user.user_entity_class". Expected scalar, but got array.',
        ];

        yield [
            ['user_entity_class' => 'InvalidClassName'],
            'Invalid configuration for path "draw_user.user_entity_class": The class ["InvalidClassName"] for the user entity must exists.',
        ];
    }
}
