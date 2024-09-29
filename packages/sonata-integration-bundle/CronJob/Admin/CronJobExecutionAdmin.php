<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Admin;

use Draw\Component\CronJob\Entity\CronJobExecution;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            ->add(
                'state',
                ChoiceFilter::class,
                [
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'multiple' => true,
                        'choices' => array_combine(
                            CronJobExecution::STATES,
                            CronJobExecution::STATES
                        ),
                    ],
                    'show_filter' => true,
                ]
            )
            ->add('force')
            ->add('executionStartedAt')
            ->add('executionEndedAt')
            ->add('executionDelay')
            ->add(
                'exitCode',
                filterOptions: [
                    'show_filter' => true,
                ]
            )
            ->add(
                'error',
                filterOptions: [
                    'show_filter' => true,
                ]
            )
        ;
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
            ->add('state')
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
                        'acknowledge' => [
                            'template' => '@DrawSonataIntegration/CronJob/CronJobExecution/list__action_acknowledge.html.twig',
                        ],
                        'delete' => [],
                    ],
                ]
            )
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('cronJob', null, ['route' => ['name' => 'show']])
            ->add('requestedAt')
            ->add('state')
            ->add('force')
            ->add('executionStartedAt')
            ->add('executionEndedAt')
            ->add('executionDelay')
            ->add('exitCode')
            ->add('error')
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('acknowledge', \sprintf('%s/acknowledge', $this->getRouterIdParameter()));
        $collection->remove('create');
        $collection->remove('edit');
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if ('show' === $action && $object?->canBeAcknowledged()) {
            $buttonList['acknowledge'] = [
                'template' => '@DrawSonataIntegration/CronJob/CronJobExecution/show__action_acknowledge.html.twig',
            ];
        }

        return $buttonList;
    }

    public function configureGridFields(array $fields): array
    {
        return array_merge(
            $fields,
            [
                'requestedAt' => [],
                'state' => [],
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
