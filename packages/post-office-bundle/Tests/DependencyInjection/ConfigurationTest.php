<?php

namespace Draw\Bundle\PostOfficeBundle\Tests\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\DependencyInjection\Configuration;
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
            'default_from' => ['enabled' => false],
            'css_inliner' => ['enabled' => false],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['default_from' => ['name' => []]],
            'Invalid type for path "draw_post_office.default_from.name". Expected scalar, but got array.',
        ];

        yield [
            ['default_from' => ['email' => []]],
            'Invalid type for path "draw_post_office.default_from.email". Expected scalar, but got array.',
        ];

        yield [
            ['default_from' => ['name' => 'Acme']],
            'The child node "email" at path "draw_post_office.default_from" must be configured.',
        ];
    }
}
