<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DrawFrameworkExtraExtension extends Extension implements PrependExtensionInterface
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

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('draw_framework_extra');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        if (!$this->isConfigEnabled($container, $config['messenger']['async_routing_configuration'])) {
            return;
        }

        $container->prependExtensionConfig(
            'framework',
            [
                'messenger' => [
                    'routing' => [
                        AsyncMessageInterface::class => 'async',
                        AsyncHighPriorityMessageInterface::class => 'async_high_priority',
                        AsyncLowPriorityMessageInterface::class => 'async_low_priority',
                    ],
                ],
            ]
        );
    }
}
