<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Controller\AdminControllerInterface;
use Draw\Bundle\SonataExtraBundle\Listener\AutoHelpSubscriber;
use Draw\Bundle\SonataExtraBundle\Listener\FixDepthMenuBuilderSubscriber;
use Draw\Bundle\SonataExtraBundle\Listener\SessionTimeoutRequestListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class DrawSonataExtraExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (!($config['fix_menu_depth']['enabled'] ?? false)) {
            $container->removeDefinition(FixDepthMenuBuilderSubscriber::class);
        }

        if (!($config['auto_help']['enabled'] ?? false)) {
            $container->removeDefinition(AutoHelpSubscriber::class);
        }

        if (!($config['session_timeout']['enabled'] ?? false)) {
            $container->removeDefinition(SessionTimeoutRequestListener::class);
        } else {
            $container
                ->getDefinition(SessionTimeoutRequestListener::class)
                ->setArgument('$delay', $config['session_timeout']['delay']);
        }

        $container->removeAlias(AdminControllerInterface::class);
    }

    public function prepend(ContainerBuilder $container): void
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
                                'list' => '@DrawSonataExtra/CRUD/show_list.html.twig',
                                'static' => '@DrawSonataExtra/CRUD/show_static.html.twig',
                            ],
                            'list' => [
                                'list' => '@DrawSonataExtra/CRUD/list_list.html.twig',
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

            if ($this->isConfigEnabled($container, $config['session_timeout'])) {
                $container->prependExtensionConfig(
                    'sonata_admin',
                    [
                        'assets' => [
                            'extra_javascripts' => [
                                'bundles/drawsonataextra/js/session_timeout.js',
                            ],
                        ],
                    ]
                );
            }
        }
    }
}
