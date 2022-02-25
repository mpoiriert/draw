<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Listener\FixDepthMenuBuilderSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class DrawSonataExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (!($config['fix_menu_depth']['enabled'] ?? false)) {
            $container->removeDefinition(FixDepthMenuBuilderSubscriber::class);
        }
    }
}
