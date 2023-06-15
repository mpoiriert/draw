<?php

namespace App\Sonata\Admin;

use App\Entity\Tag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'model_class' => Tag::class,
        'manager_type' => 'orm',
        'group' => 'Tag',
        'pager_type' => 'simple',
    ]
)]
class TagAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('label')
            ->add('active');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('label')
            ->add('active');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('label')
            ->add('active');
    }

    public function configureGridFields(array $fields): array
    {
        return array_merge(
            $fields,
            [
                'id' => [],
                'label' => [],
                'active' => [],
                'actions' => [
                    'type' => 'actions',
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
