<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use Draw\Component\Profiling\ProfilerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawTesterExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(ProfilerInterface::class)
            ->addTag(ProfilerInterface::class);

        $container
            ->registerForAutoconfiguration(ExpressionEvaluator::class)
            ->addTag(ExpressionEvaluator::class);

        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');
        $fileLoader = new Loader\XmlFileLoader($container, $fileLocator);

        $this->configureProfiling($config['profiling'], $fileLoader, $container);

        $fileLoader->load('filter.xml');
    }

    private function configureProfiling($config, Loader\FileLoader $fileLoader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $fileLoader->load('profiling.xml');
    }
}
