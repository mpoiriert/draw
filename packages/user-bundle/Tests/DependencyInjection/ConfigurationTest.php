<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use App\Entity\User;
use Draw\Bundle\UserBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
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
            ],
            'email_writers' => [
                'enabled' => false,
                'forgot_password' => [
                    'enabled' => true,
                ],
                'onboarding' => [
                    'enabled' => true,
                    'expiration_delay' => '+ 7 days',
                ],
                'password_change_requested' => [
                    'enabled' => true,
                ],
                'to_user' => [
                    'enabled' => true,
                ],
            ],
            'onboarding' => [
                'enabled' => false,
            ],
            'enforce_2fa' => [
                'enabled' => false,
                'enable_route' => 'admin_app_user_enable-2fa',
                'enforcing_roles' => [],
            ],
            'password_change_enforcer' => [
                'enabled' => false,
                'change_password_route' => 'admin_change_password',
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
