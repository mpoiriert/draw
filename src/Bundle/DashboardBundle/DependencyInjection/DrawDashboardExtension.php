<?php namespace Draw\Bundle\DashboardBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawDashboardExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('draw_dashboard.title', $config['title']);
        $container->setParameter('draw_dashboard.menu', $config['menu']);
        $container->setParameter('draw_dashboard.toolbar', $config['toolbar']);
    }
}