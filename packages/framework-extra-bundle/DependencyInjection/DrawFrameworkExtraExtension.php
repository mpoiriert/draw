<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DrawFrameworkExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $this->configureProcess($config['process'], $loader, $container);
    }

    private function configureProcess(array $config, PhpFileLoader $loader, ContainerBuilder $containerBuilder): void
    {
        if (!$this->isConfigEnabled($containerBuilder, $config)) {
            return;
        }

        $loader->load('process.php');
    }
}
