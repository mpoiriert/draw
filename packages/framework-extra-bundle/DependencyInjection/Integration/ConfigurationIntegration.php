<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Application\Configuration\DoctrineConfigurationRegistry;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ConfigurationIntegration implements IntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'configuration';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $directory = dirname(
            (new ReflectionClass(DoctrineConfigurationRegistry::class))->getFileName()
        );

        $definition = (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $exclude = [
            $directory.'/Entity',
        ];

        $namespace = 'Draw\\Component\\Application\\Configuration\\';

        $loader->registerClasses(
            $definition,
            $namespace,
            $directory,
            $exclude
        );

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.configuration.'
        );

        $container->setAlias(
            ConfigurationRegistryInterface::class,
            DoctrineConfigurationRegistry::class
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }

    public function prepend(ContainerBuilder $container, array $config): void
    {
        $this->assertHasExtension($container, 'doctrine');

        $reflection = new ReflectionClass(Config::class);

        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'DrawConfiguration' => [
                            'is_bundle' => false,
                            'type' => 'annotation',
                            'dir' => dirname($reflection->getFileName()),
                            'prefix' => $reflection->getNamespaceName(),
                        ],
                    ],
                ],
            ]
        );
    }
}
