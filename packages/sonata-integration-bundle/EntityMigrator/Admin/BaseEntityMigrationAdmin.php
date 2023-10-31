<?php

namespace Draw\Bundle\SonataIntegrationBundle\EntityMigrator\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

abstract class BaseEntityMigrationAdmin extends AbstractAdmin
{
    public const ADMIN = [
        'manager_type' => 'orm',
        'pager_type' => 'simple',
        'group' => 'Entity Migrator',
        'icon' => 'fas fa-cogs',
    ];

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('entity')
            ->add('migration')
            ->add('state');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('entity')
            ->add('migration')
            ->add('state')
            ->add(
                'transitionLogs',
                fieldDescriptionOptions: [
                    'template' => '@DrawSonataIntegration/EntityMigrator/BaseEntityMigration/show_transition_logs.html.twig',
                ]
            )
            ->add('createdAt');
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->clearExcept('list');
    }
}
