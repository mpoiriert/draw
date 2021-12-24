<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes\UTCDateTimeImmutableType;
use Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes\UTCDateTimeType;
use Draw\Bundle\SonataExtraBundle\Listener\TimeZoneSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class DrawSonataExtraExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (!($config['user_timezone']['enabled'] ?? false)) {
            $container->removeDefinition(TimeZoneSubscriber::class);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $config = array_merge_recursive(...$container->getExtensionConfig('draw_sonata_extra'));
        if (!($config['user_timezone']['enabled'] ?? false)) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    'datetime' => UTCDateTimeType::class,
                    'datetime_immutable' => UTCDateTimeImmutableType::class,
                    'datetimetz' => UTCDateTimeType::class,
                    'datetimetz_immutable' => UTCDateTimeImmutableType::class,
                ],
            ],
        ]);
    }
}
