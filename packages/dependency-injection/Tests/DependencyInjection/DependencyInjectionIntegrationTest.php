<?php

namespace Draw\Component\DependencyInjection\Tests\DependencyInjection;

use Draw\Component\DependencyInjection\DependencyInjection\DependencyInjectionIntegration;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;

class DependencyInjectionIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new DependencyInjectionIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'dependency_injection';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public static function provideTestLoad(): iterable
    {
        yield [];
    }
}
