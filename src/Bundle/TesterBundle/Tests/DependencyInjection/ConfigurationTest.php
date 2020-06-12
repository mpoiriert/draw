<?php

namespace Draw\Bundle\TesterBundle\Tests\DependencyInjection;

use Draw\Bundle\TesterBundle\DependencyInjection\Configuration;
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
        return ['profiling' => ['enabled' => true]];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        return [];
    }
}
