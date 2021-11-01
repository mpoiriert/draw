<?php

namespace Draw\Bundle\MessengerBundle\DependencyInjection;

use Draw\Bundle\MessengerBundle\Entity\MessengerMessage;
use Draw\Bundle\MessengerBundle\Sonata\Admin\MessengerMessageAdmin;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class DrawMessengerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (null !== $config['transport_service_name']) {
            $container->setAlias(DrawTransport::class, $config['transport_service_name']);
        }

        $this->configureSonata($config['sonata'], $loader, $container);
    }

    private function configureSonata(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $transportMapping = [];
        foreach ($config['transports'] as $transportInfo) {
            $transportMapping[$transportInfo['queue_name']] = $transportInfo['transport_name'];
        }

        $fileLoader->load('sonata.xml');

        $container
            ->getDefinition(MessengerMessageAdmin::class)
            ->setArguments([
                null,
                MessengerMessage::class,
                $config['controller_class'],
            ])
            ->addTag(
                'sonata.admin',
                array_intersect_key(
                    $config,
                    array_flip(['group', 'icon', 'label', 'pager_type'])
                ) + ['manager_type' => 'orm']
            )
            ->addMethodCall('setTemplate', ['show', '@DrawMessenger/Sonata/Admin/MessengerMessage/show.html.twig'])
            ->addMethodCall(
                'inject',
                [
                    '$transportMapping' => $transportMapping,
                    '$receiverLocator' => new Reference('messenger.receiver_locator'),
                ]
            );
    }
}
