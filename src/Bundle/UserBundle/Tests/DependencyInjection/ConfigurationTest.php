<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
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
            'email_writers' => [
                'enabled' => false,
            ],
            'sonata' => [
                'enabled' => true,
                'user_admin_code' => UserAdmin::class,
                '2fa' => [
                    'enabled' => false,
                    'field_positions' => [
                        '2fa_enabled' => [
                            'list' => ListMapper::NAME_ACTIONS,
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
            'jwt_authenticator' => [
                'enabled' => false,
                'query_parameters' => [
                    'enabled' => true,
                    'accepted_keys' => ['_auth'],
                ],
            ],
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

        yield [
            ['jwt_authenticator' => []],
            'The child node "key" at path "draw_user.jwt_authenticator" must be configured.',
        ];
    }
}
