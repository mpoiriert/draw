<?php

namespace Draw\Bundle\SonataIntegrationBundle\EntityMigrator\Admin;

use Draw\Component\EntityMigrator\Entity\BaseEntityMigration;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

abstract class BaseEntityMigrationAdmin extends AbstractAdmin
{
    public const ADMIN = [
        'manager_type' => 'orm',
        'pager_type' => 'simple',
        'group' => 'Entity Migrator',
        'icon' => 'fas fa-cogs',
        'translation_domain' => 'DrawEntityMigratorAdmin',
    ];

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add(
                'migration',
                ModelFilter::class,
                [
                    'field_type' => ModelAutocompleteType::class,
                    'field_options' => [
                        'property' => 'name',
                    ],
                    'show_filter' => true,
                ]
            )
            ->add(
                'state',
                ChoiceFilter::class,
                [
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'multiple' => true,
                        'choices' => array_combine(
                            BaseEntityMigration::STATES,
                            BaseEntityMigration::STATES
                        ),
                    ],
                    'show_filter' => true,
                ]
            )
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('entity')
            ->add('migration')
            ->add('state')
        ;
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
            ->add('createdAt')
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->clearExcept(['list', 'show'])
        ;
    }
}
