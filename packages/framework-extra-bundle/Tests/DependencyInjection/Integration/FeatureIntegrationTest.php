<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\FeatureIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\Application\Feature\FeatureInitializer;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\FeatureIntegration
 *
 * @property ConfigurationIntegration $integration
 */
class FeatureIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new FeatureIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'feature';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.feature.feature_initializer',
                    [
                        FeatureInitializer::class,
                    ]
                ),
            ],
            [
            ],
            [],
        ];
    }
}
