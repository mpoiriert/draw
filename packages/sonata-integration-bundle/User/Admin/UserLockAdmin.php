<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Admin;

use Draw\Bundle\SonataExtraBundle\Form\Extension\Core\Type\SingleLineDateTimeType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UserLockAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add(
            'user',
            ModelFilter::class,
            [
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'email',
                ],
            ]
        );
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->add('reason')
            ->add('user')
            ->add('createdAt')
            ->add('lockOn')
            ->add('expiresAt')
            ->add('unlockUntil')
            ->add('active', 'boolean', ['inverse' => true])
            ->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                    ],
                ]
            )
        ;
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('active', CheckboxType::class, ['disabled' => true, 'required' => false])
            ->add('reason', null, ['disabled' => true, 'required' => false])
            ->add(
                'createdAt',
                SingleLineDateTimeType::class,
                [
                    'disabled' => true,
                    'required' => false,
                ]
            )
            ->add(
                'lockOn',
                SingleLineDateTimeType::class,
                [
                    'disabled' => true,
                    'required' => false,
                ]
            )
            ->add(
                'expiresAt',
                SingleLineDateTimeType::class,
                [
                    'disabled' => true,
                    'required' => false,
                ]
            )
            ->add(
                'unlockUntil',
                SingleLineDateTimeType::class,
                [
                    'required' => false,
                ]
            )
        ;
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('user')
            ->add('active', 'boolean', ['inverse' => true])
            ->add('reason')
            ->add('createdAt')
            ->add('lockOn')
            ->add('expiresAt')
            ->add('unlockUntil')
            ->add(
                'explanation',
                null,
                [
                    'virtual_field' => true,
                    'template' => '@DrawSonataIntegration/UserLock/CRUD/show_reason_details.html.twig',
                ]
            )
        ;
    }

    public function configureGridFields(array $fields): array
    {
        return array_merge(
            $fields,
            [
                'reason' => [],
                'active' => [
                    'type' => 'boolean',
                    'options' => [
                        'inverse' => true,
                    ],
                ],
                'createdAt' => [],
                'lockOn' => [],
                'expiresAt' => [],
                'unlockUntil' => [],
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

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit', 'show']);
    }
}
