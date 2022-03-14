<?php

namespace Draw\Bundle\ApplicationBundle\DependencyInjection;

use Draw\Bundle\ApplicationBundle\Configuration\Repository\ConfigRepository;
use Draw\Bundle\ApplicationBundle\Configuration\Sonata\Admin\ConfigAdmin;
use Draw\Component\Core\Configuration\SonataAdminNodeConfiguration;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DrawApplicationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $this->configureConfiguration($config['configuration'], $loader, $container);
        $this->configureVersioning($config['versioning'], $loader, $container);
    }

    private function configureConfiguration(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('configuration.xml');

        $containerBuilder->setAlias(
            ConfigurationRegistryInterface::class,
            ConfigRepository::class
        );

        if (!$config['sonata']['enabled']) {
            $containerBuilder->removeDefinition(ConfigAdmin::class);

            return;
        }

        $containerBuilder->addDefinitions([
            ConfigAdmin::class => SonataAdminNodeConfiguration::configureFromConfiguration(
                new Definition(ConfigAdmin::class),
                $config['sonata']
            )
                ->setAutoconfigured(true)
                ->setAutowired(true),
        ]);
    }

    private function configureVersioning(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('versioning.xml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('draw_application');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        if (!$this->isConfigEnabled($container, $config['configuration'])) {
            return;
        }

        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'DrawApplication' => [
                            'is_bundle' => false,
                            'type' => 'annotation',
                            'dir' => __DIR__.'/../Configuration/Entity',
                            'prefix' => 'Draw\Bundle\ApplicationBundle\Configuration\Entity',
                        ],
                    ],
                ],
            ]
        );
    }
}
