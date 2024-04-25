<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Block\Event\FinalizeContextEvent;
use Draw\Bundle\SonataExtraBundle\Block\MonitoringBlockService;
use Draw\Bundle\SonataExtraBundle\Controller\AdminControllerInterface;
use Draw\Bundle\SonataExtraBundle\EventListener\AutoHelpListener;
use Draw\Bundle\SonataExtraBundle\EventListener\FixDepthMenuBuilderListener;
use Draw\Bundle\SonataExtraBundle\EventListener\PreObjectDeleteBatchEventEventListener;
use Draw\Bundle\SonataExtraBundle\EventListener\SessionTimeoutRequestListener;
use Draw\Bundle\SonataExtraBundle\Extension\AutoActionExtension;
use Draw\Bundle\SonataExtraBundle\Extension\ListFieldPriorityExtension;
use Draw\Bundle\SonataExtraBundle\FieldDescriptionFactory\SubClassFieldDescriptionFactory;
use Draw\Bundle\SonataExtraBundle\Notifier\Channel\SonataChannel;
use Draw\Bundle\SonataExtraBundle\PreventDelete\Extension\PreventDeleteExtension;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDelete;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Draw\Bundle\SonataExtraBundle\PreventDelete\Security\Voter\PreventDeleteVoter;
use Draw\Bundle\SonataExtraBundle\Security\Handler\CanSecurityHandler;
use Draw\Bundle\SonataExtraBundle\Security\Voter\DefaultCanVoter;
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

        if (!($config['auto_action']['enabled'] ?? false)) {
            $container->removeDefinition(AutoActionExtension::class);
        } else {
            $container
                ->getDefinition(AutoActionExtension::class)
                ->setArgument('$ignoreAdmins', $config['auto_action']['ignore_admins'])
                ->setArgument('$actions', $config['auto_action']['actions']);
        }

        if (!($config['auto_help']['enabled'] ?? false)) {
            $container->removeDefinition(AutoHelpListener::class);
        }

        if (!($config['batch_delete_check']['enabled'] ?? false)) {
            $container->removeDefinition(PreObjectDeleteBatchEventEventListener::class);
        }

        if (!($config['fix_menu_depth']['enabled'] ?? false)) {
            $container->removeDefinition(FixDepthMenuBuilderListener::class);
        }

        if (!$config['prevent_delete_extension']['enabled']) {
            $container->removeDefinition(PreventDeleteExtension::class);
        } else {
            $container
                ->getDefinition(PreventDeleteExtension::class)
                ->setArgument('$restrictToRole', $config['prevent_delete_extension']['restrict_to_role']);
        }

        if (!($config['notifier']['enabled'] ?? false)) {
            $container->removeDefinition(SonataChannel::class);
        }

        if (!($config['can_security_handler']['enabled'] ?? false)) {
            $container->removeDefinition(CanSecurityHandler::class);
            $container->removeDefinition(DefaultCanVoter::class);
            $container->removeDefinition(PreventDeleteVoter::class);
            $container->removeDefinition(PreventDeleteRelationLoader::class);
        } else {
            if (!$config['can_security_handler']['grant_by_default']) {
                $container->removeDefinition(DefaultCanVoter::class);
            }

            if (!$config['can_security_handler']['prevent_delete_voter']['enabled']) {
                $container->removeDefinition(PreventDeleteVoter::class);
                $container->removeDefinition(PreventDeleteRelationLoader::class);
                $container->removeDefinition(PreventDeleteExtension::class);
            } else {
                $container->getDefinition(PreventDeleteRelationLoader::class)
                    ->setArgument(
                        '$configuration',
                        $config['can_security_handler']['prevent_delete_voter']['entities']
                    )
                    ->setArgument(
                        '$useManager',
                        $config['can_security_handler']['prevent_delete_voter']['use_manager']
                    )
                    ->setArgument(
                        '$preventDeleteFromAllRelations',
                        $config['can_security_handler']['prevent_delete_voter']['prevent_delete_from_all_relations']
                    );
                if (!$config['can_security_handler']['prevent_delete_voter']['use_cache']) {
                    $container->getDefinition(PreventDeleteRelationLoader::class)
                        ->setArgument('$cacheDirectory', null);
                }
            }
        }

        if (!($config['list_field_priority']['enabled'] ?? false)) {
            $container->removeDefinition(ListFieldPriorityExtension::class);
        } else {
            $defaultFieldPriorities = [];

            foreach ($config['list_field_priority']['default_field_priorities'] as $key => $priority) {
                $defaultFieldPriorities[$key] = $priority;
            }

            $container
                ->getDefinition(ListFieldPriorityExtension::class)
                ->setArguments([
                    '$defaultMaxField' => $config['list_field_priority']['default_max_field'],
                    '$defaultFieldPriorities' => $defaultFieldPriorities,
                ]);
        }

        if (!($config['session_timeout']['enabled'] ?? false)) {
            $container->removeDefinition(SessionTimeoutRequestListener::class);
        } else {
            $container
                ->getDefinition(SessionTimeoutRequestListener::class)
                ->setArgument('$delay', $config['session_timeout']['delay']);
        }

        $container->removeDefinition(FinalizeContextEvent::class);
        $container->removeDefinition(PreventDelete::class);
        $container->removeDefinition(SubClassFieldDescriptionFactory::class);

        $container->removeAlias(AdminControllerInterface::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->hasTag('container.excluded')) {
                $container->removeDefinition($id);
            }
        }
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
                                'url' => '@DrawSonataExtra/CRUD/show_url.html.twig',
                            ],
                            'list' => [
                                'list' => '@DrawSonataExtra/CRUD/list_list.html.twig',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sonata_block')) {
            $container->prependExtensionConfig(
                'sonata_block',
                [
                    'blocks' => [
                        MonitoringBlockService::class => null,
                    ],
                ]
            );
        }

        if ($container->hasExtension('sonata_admin')) {
            if ($config['install_assets']) {
                $container->prependExtensionConfig(
                    'sonata_admin',
                    [
                        'assets' => [
                            'extra_javascripts' => [
                                'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.js',
                                'bundles/drawsonataextra/js/json_viewer.js',
                            ],
                            'extra_stylesheets' => [
                                'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.css',
                            ],
                        ],
                    ]
                );
            }

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
