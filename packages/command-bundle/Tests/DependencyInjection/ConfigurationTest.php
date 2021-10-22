<?php

namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection;

use Draw\Bundle\CommandBundle\Authentication\SystemAuthenticator;
use Draw\Bundle\CommandBundle\DependencyInjection\Configuration;
use Draw\Bundle\CommandBundle\Sonata\Controller\ExecutionController;
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
            'doctrine' => [
                'enabled' => true,
                'log_execution' => true,
            ],
            'sonata' => [
                'enabled' => true,
                'group' => 'Command',
                'controller_class' => ExecutionController::class,
                'icon' => "<i class='fa fa-terminal'></i>",
                'label' => 'Execution',
                'pager_type' => 'simple',
            ],
            'authentication' => [
                'enabled' => false,
                'system_authentication_service' => SystemAuthenticator::class,
                'system_auto_login' => false,
            ],
            'commands' => [],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['doctrine' => ['log_execution' => []]],
            'Invalid type for path "draw_command.doctrine.log_execution". Expected scalar, but got array.',
        ];

        yield [
            ['sonata' => ['group' => []]],
            'Invalid type for path "draw_command.sonata.group". Expected scalar, but got array.',
        ];

        yield [
            ['sonata' => ['controller_class' => []]],
            'Invalid type for path "draw_command.sonata.controller_class". Expected scalar, but got array.',
        ];

        yield [
            ['sonata' => ['icon' => []]],
            'Invalid type for path "draw_command.sonata.icon". Expected scalar, but got array.',
        ];

        yield [
            ['sonata' => ['label' => []]],
            'Invalid type for path "draw_command.sonata.label". Expected scalar, but got array.',
        ];

        yield [
            ['sonata' => ['pager_type' => 'invalid']],
            'The value "invalid" is not allowed for path "draw_command.sonata.pager_type". Permissible values: "default", "simple"',
        ];

        yield [
            ['authentication' => ['system_authentication_service' => []]],
            'Invalid type for path "draw_command.authentication.system_authentication_service". Expected scalar, but got array.',
        ];

        yield [
            ['authentication' => ['system_auto_login' => []]],
            'Invalid type for path "draw_command.authentication.system_auto_login". Expected boolean, but got array.',
        ];
    }
}
