<?php namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawTesterExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $fileLoader = new XmlFileLoader($container, $fileLocator);
        if($mergedConfig['profiling']['enabled']) {
            $fileLoader->load('profiling.xml');
        }
    }
}