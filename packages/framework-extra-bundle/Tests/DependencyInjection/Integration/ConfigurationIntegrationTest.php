<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\Application\Configuration\DoctrineConfigurationRegistry;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @property ConfigurationIntegration $integration
 */
#[CoversClass(ConfigurationIntegration::class)]
class ConfigurationIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new ConfigurationIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'configuration';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public function testPrependNoDoctrineExtension(): void
    {
        static::expectExceptionMessage(
            'You must have the extension [doctrine] available to configuration [draw_framework_extra.configuration]'
        );

        $this->integration->prepend(
            new ContainerBuilder(),
            []
        );
    }

    public function testPrepend(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->registerExtension(new DoctrineExtension());

        $this->integration->prepend(
            $containerBuilder,
            []
        );

        $reflection = new \ReflectionClass(Config::class);

        static::assertContainerExtensionConfiguration(
            $containerBuilder,
            [
                'doctrine' => [
                    [
                        'orm' => [
                            'mappings' => [
                                'DrawConfiguration' => [
                                    'is_bundle' => false,
                                    'type' => 'attribute',
                                    'dir' => \dirname($reflection->getFileName()),
                                    'prefix' => $reflection->getNamespaceName(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.configuration.doctrine_configuration_registry',
                    [
                        DoctrineConfigurationRegistry::class,
                    ]
                ),
            ],
            [
                DoctrineConfigurationRegistry::class => [
                    ConfigurationRegistryInterface::class,
                ],
            ],
            [],
        ];
    }
}
