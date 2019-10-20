<?php namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Component\Profiling\ProfilerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawTesterExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(ProfilerInterface::class)
            ->addTag(ProfilerInterface::class);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $fileLoader = new Loader\XmlFileLoader($container, $fileLocator);
        if ($mergedConfig['profiling']['enabled']) {
            $fileLoader->load('profiling.xml');
        }
    }
}