<?php

namespace App\Sonata\Admin;

use App\Entity\Tag;
use Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @TagSonataAdmin(
 *     group="Tag",
 *     manager_type="orm",
 *     pager_type="simple",
 *     model_class=Tag::class
 * )
 */
class TagAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('label')
            ->add('active')
            ->add(
                $list::NAME_ACTIONS,
                null,
                [
                    'label' => 'Action',
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
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
            ->add('id')
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
