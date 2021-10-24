<?php

namespace Draw\Bundle\MessengerBundle\Tests\DependencyInjection;

use Draw\Bundle\MessengerBundle\DependencyInjection\Configuration;
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
            'transport_service_name' => 'messenger.transport.draw',
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['transport_service_name' => []],
            'Invalid type for path "draw_messenger.transport_service_name". Expected scalar, but got array.',
        ];
    }
}
