<?php

namespace Draw\Bundle\SonataIntegrationBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Configuration\SonataAdminNodeConfiguration;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Admin\MessengerMessageAdmin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class DrawSonataIntegrationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->configureMessenger($config['messenger'], $loader, $container);
    }

    private function configureMessenger(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                MessengerMessageAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(MessengerMessageAdmin::class),
                    $config['admin']
                )
            )
            ->addMethodCall(
                'setTemplate',
                ['show', '@DrawSonataIntegration/Messenger/Message/show.html.twig']
            )
            ->addMethodCall(
                'inject',
                [
                    '$queueNames' => $config['queue_names'],
                    '$envelopeFinder' => new Reference('draw.messenger.envelope_finder'),
                ]
            );
    }
}
