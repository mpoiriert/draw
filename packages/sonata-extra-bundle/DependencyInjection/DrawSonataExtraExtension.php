<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes\UtcDateTimeImmutableType;
use Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes\UtcDateTimeType;
use Draw\Bundle\SonataExtraBundle\Doctrine\DBALTypes\UtcTimeImmutableType;
use Draw\Bundle\SonataExtraBundle\Extension\AutoHelpExtension;
use Draw\Bundle\SonataExtraBundle\Listener\FixDepthMenuBuilderSubscriber;
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

        if (!($config['fix_menu_depth']['enabled'] ?? false)) {
            $container->removeDefinition(FixDepthMenuBuilderSubscriber::class);
        }

        if (!($config['auto_help']['enabled'] ?? false)) {
            $container->removeDefinition(AutoHelpExtension::class);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('draw_sonata_extra');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig(
                'twig',
                [
                    'paths' => [
                        realpath(__DIR__.'/../Resources/SonataAdminBundle/views') => 'SonataAdmin',
                    ],
                ]
            );
        }

        if ($container->hasExtension('sonata_doctrine_orm_admin')) {
            $container->prependExtensionConfig(
                'sonata_doctrine_orm_admin',
                [
                    'templates' => [
                        'types' => [
                            'show' => [
                                'actions' => '@DrawSonataExtra/CRUD/show_actions.html.twig',
                                'json' => '@DrawSonataExtra/CRUD/show_json.html.twig',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sonata_admin')) {
            $container->prependExtensionConfig(
                'sonata_admin',
                [
                    'assets' => [
                        'extra_javascripts' => [
                            'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.js',
                        ],
                        'extra_stylesheets' => [
                            'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.css',
                        ],
                    ],
                ]
            );
        }

        if (!$this->isConfigEnabled($container, $config['user_timezone'])) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    'datetime' => UtcDateTimeType::class,
                    'datetime_immutable' => UtcDateTimeImmutableType::class,
                    'time' => UtcDateTimeType::class,
                    'time_immutable' => UtcTimeImmutableType::class,
                ],
            ],
        ]);
    }
}
