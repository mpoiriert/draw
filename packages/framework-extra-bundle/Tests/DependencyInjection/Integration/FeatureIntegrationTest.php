<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\FeatureIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\Application\Feature\FeatureInitializer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @property ConfigurationIntegration $integration
 */
#[CoversClass(FeatureIntegration::class)]
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

    public static function provideTestLoad(): iterable
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
