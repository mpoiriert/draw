<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\Configuration;
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
            'auto_help' => [
                'enabled' => false,
            ],
            'can_security_handler' => [
                'enabled' => false,
                'grant_by_default' => true,
            ],
            'fix_menu_depth' => [
                'enabled' => false,
            ],
            'session_timeout' => [
                'enabled' => false,
                'delay' => 3600,
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['fix_menu_depth' => ['enabled' => []]],
            'Invalid type for path "draw_sonata_extra.fix_menu_depth.enabled". Expected bool, but got array.',
        ];

        yield [
            ['fix_menu_depth' => ['enabled' => 'test']],
            'Invalid type for path "draw_sonata_extra.fix_menu_depth.enabled". Expected bool, but got string.',
        ];

        yield [
            ['session_timeout' => ['delay' => 'test']],
            'Invalid type for path "draw_sonata_extra.session_timeout.delay". Expected int, but got string.',
        ];
    }
}
