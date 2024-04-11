<?php

namespace Draw\Bundle\SonataImportBundle\DependencyInjection;

use Draw\Bundle\SonataImportBundle\Column\ColumnBuilder\ColumnBuilderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawSonataImportExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $container
            ->registerForAutoconfiguration(ColumnBuilderInterface::class)
            ->addTag('draw.sonata_import.extractor');

        $container->setParameter('draw.sonata_import.classes', $mergedConfig['classes']);
    }
}
