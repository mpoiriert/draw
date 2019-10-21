<?php namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
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
            'sonata' => [
                'enabled' => true,
                'user_admin_code' => UserAdmin::class,
            ],
            'encrypt_password_listener' => true,
            'user_entity_class' => User::class
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['sonata' => ['user_admin_code' => []]],
            'Invalid type for path "draw_user.sonata.user_admin_code". Expected scalar, but got array.'
        ];

        yield [
            ['encrypt_password_listener' => []],
            'Invalid type for path "draw_user.encrypt_password_listener". Expected boolean, but got array.'
        ];

        yield [
            ['user_entity_class' => []],
            'Invalid type for path "draw_user.user_entity_class". Expected scalar, but got array.'
        ];

        yield [
            ['user_entity_class' => 'InvalidClassName'],
            'Invalid configuration for path "draw_user.user_entity_class": The class ["InvalidClassName"] for the user entity must exists.'
        ];
    }
}