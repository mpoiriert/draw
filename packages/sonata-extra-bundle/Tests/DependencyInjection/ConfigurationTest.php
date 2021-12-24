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
            'user_timezone' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['user_timezone' => ['enabled' => []]],
            'Invalid type for path "draw_sonata_extra.user_timezone.enabled". Expected bool, but got array.',
        ];

        yield [
            ['user_timezone' => ['enabled' => 'test']],
            'Invalid type for path "draw_sonata_extra.user_timezone.enabled". Expected bool, but got string.',
        ];
    }
}
