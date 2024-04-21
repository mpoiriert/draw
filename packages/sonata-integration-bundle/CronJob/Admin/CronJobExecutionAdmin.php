<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;

class CronJobExecutionAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'cronJob',
                ModelFilter::class,
                [
                    'field_type' => ModelAutocompleteType::class,
                    'field_options' => [
                        'property' => 'name',
                    ],
                    'show_filter' => true,
                ]
            )
            ->add('requestedAt')
            ->add('force')
            ->add('executionStartedAt')
            ->add('executionEndedAt')
            ->add('executionDelay')
            ->add(
                'exitCode',
                filterOptions: [
                    'show_filter' => true,
                ]
            );

    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add(
                'cronJob',
                fieldDescriptionOptions: [
                    'sortable' => 'cronJob.name',
                ]
            )
            ->add('requestedAt')
            ->add('force')
            ->add('executionStartedAt')
            ->add('executionEndedAt')
            ->add('executionDelay')
            ->add('exitCode')
            ->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'actions' => [
                        'show' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('requestedAt')
            ->add('force')
            ->add('executionStartedAt')
            ->add('executionEndedAt')
            ->add('executionDelay')
            ->add('exitCode')
            ->add('error', 'json');
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show', 'delete']);
    }

    public function configureGridFields(array $fields): array
    {
        return array_merge(
            $fields,
            [
                'requestedAt' => [],
                'force' => [],
                'executionStartedAt' => [],
                'executionEndedAt' => [],
                'executionDelay' => [],
                'exitCode' => [],
                'actions' => [
                    'type' => ListMapper::TYPE_ACTIONS,
                    'options' => [
                        'virtual_field' => true,
                        'admin' => $this,
                        'actions' => [
                            'show' => [
                                'label' => 'Show',
                                'icon' => 'fa-eye',
                                'route_object' => 'show',
                                'check_callback' => fn (object $object) => $this->hasAccess('show', $object),
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
