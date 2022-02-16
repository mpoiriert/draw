<?php

namespace Draw\Bundle\PostOfficeBundle\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\EmailWriter\DefaultFromEmailWriter;
use Draw\Bundle\PostOfficeBundle\EmailWriter\EmailWriterInterface;
use Draw\Bundle\PostOfficeBundle\Listener\EmailCssInlinerListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Mime\Address;

class DrawPostOfficeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container
            ->registerForAutoconfiguration(EmailWriterInterface::class)
            ->addTag(EmailWriterInterface::class);

        // This is to remove singly implemented aliases
        $container->removeAlias(EmailWriterInterface::class);
        $this->configureCssInliner($config['css_inliner'], $container);
        $this->configureDefaultFrom($config['default_from'], $container);
    }

    private function configureCssInliner(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(EmailCssInlinerListener::class);
        }
    }

    private function configureDefaultFrom($config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(DefaultFromEmailWriter::class);

            return;
        }

        $container->getDefinition(DefaultFromEmailWriter::class)
            ->setArgument('$defaultFrom', new Reference('draw_post_office.default_from'));

        $definition = (new Definition(Address::class))
            ->setArguments([$config['email'], $config['name'] ?? '']);

        $container->setDefinition('draw_post_office.default_from', $definition);
    }
}
