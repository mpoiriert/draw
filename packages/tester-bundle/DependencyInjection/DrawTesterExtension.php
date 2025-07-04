<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Bundle\TesterBundle\Messenger\HandleMessagesMappingProvider;
use Draw\Bundle\TesterBundle\Messenger\HandlerConfigurationDumper;
use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use Draw\Component\Profiling\ProfilerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class DrawTesterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $container
            ->registerForAutoconfiguration(ProfilerInterface::class)
            ->addTag(ProfilerInterface::class)
        ;

        $container
            ->registerForAutoconfiguration(ExpressionEvaluator::class)
            ->addTag(ExpressionEvaluator::class)
        ;

        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');
        $fileLoader = new Loader\XmlFileLoader($container, $fileLocator);

        $this->configureProfiling($config['profiling'], $fileLoader, $container);

        $fileLoader->load('filter.xml');

        $container->setDefinition(
            HandleMessagesMappingProvider::class,
            new Definition(HandleMessagesMappingProvider::class)
        )->setPublic(true);

        $container->setDefinition(
            HandlerConfigurationDumper::class,
            new Definition(HandlerConfigurationDumper::class)
        )->setPublic(true);
    }

    private function configureProfiling($config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $fileLoader->load('profiling.xml');
    }
}
