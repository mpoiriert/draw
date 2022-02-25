<?php

namespace Draw\Bundle\MessengerBundle\DependencyInjection;

use Draw\Bundle\MessengerBundle\Broker\EventListener\DefaultValuesListener;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageInterface;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageTagInterface;
use Draw\Bundle\MessengerBundle\EventListener\StopWorkerOnNewVersionListener;
use Draw\Bundle\MessengerBundle\Sonata\Admin\MessengerMessageAdmin;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\RelativeDateTimeFilter;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class DrawMessengerExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (null !== $config['transport_service_name']) {
            $container->setAlias(DrawTransport::class, $config['transport_service_name']);
        }

        $this->configureWorkerVersionMonitoring($config['worker_version_monitoring'], $loader, $container);
        $this->configureBroker($config['broker'], $loader, $container);
        $this->configureSonata($config['sonata'], $loader, $container);
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('draw_messenger');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        if (!$this->isConfigEnabled($container, $config['sonata'])) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'resolve_target_entities' => [
                    DrawMessageInterface::class => $config['sonata']['entity_class'],
                    DrawMessageTagInterface::class => $config['sonata']['tag_entity_class'],
                ],
            ],
        ]);
    }

    private function configureWorkerVersionMonitoring(
        array $config,
        Loader\FileLoader $fileLoader,
        ContainerBuilder $container
    ): void {
        if (!$this->isConfigEnabled($container, $config)) {
            $container->removeDefinition(StopWorkerOnNewVersionListener::class);

            return;
        }

        $container->getDefinition(StopWorkerOnNewVersionListener::class)
            ->setArgument(
                '$versionVerification',
                new Reference($config['version_verification_service'])
            );
    }

    private function configureBroker(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $fileLoader->load('broker.xml');

        $container->setParameter('draw.messenger.broker.symfony_console_path', $config['symfony_console_path']);

        $defaultOptions = [];
        foreach ($config['default_options'] as $options) {
            $defaultOptions[$options['name']] = $options['value'];
        }

        $container->getDefinition(DefaultValuesListener::class)
            ->setArgument('$receivers', $config['receivers'])
            ->setArgument('$defaultOptions', $defaultOptions);
    }

    private function configureSonata(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
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
                $config['entity_class'],
                $config['controller_class'],
            ])
            ->addTag(
                'sonata.admin',
                array_intersect_key(
                    $config,
                    array_flip(['group', 'icon', 'label', 'pager_type'])
                ) + ['manager_type' => 'orm']
            )
            ->addMethodCall('setTemplate',
                ['show', '@DrawMessenger/Sonata/Admin/MessengerMessage/show.html.twig'])
            ->addMethodCall(
                'inject',
                [
                    '$transportMapping' => $transportMapping,
                    '$receiverLocator' => new Reference('messenger.receiver_locator'),
                    '$relativeDateTimeFilter' => new Reference(RelativeDateTimeFilter::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ]
            );
    }
}
