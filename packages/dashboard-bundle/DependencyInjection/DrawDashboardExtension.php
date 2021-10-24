<?php

namespace Draw\Bundle\DashboardBundle\DependencyInjection;

use Draw\Bundle\DashboardBundle\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class DrawDashboardExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container
            ->registerForAutoconfiguration(ExpressionFunctionProviderInterface::class)
            ->addTag(ExpressionFunctionProviderInterface::class);

        $container->setParameter('draw_dashboard.title', $config['title']);
        $container->setParameter('draw_dashboard.menu', $config['menu']);
        $container->setParameter('draw_dashboard.toolbar', $config['toolbar']);
    }
}
