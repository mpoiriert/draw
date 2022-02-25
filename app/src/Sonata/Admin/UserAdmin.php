<?php

namespace App\Sonata\Admin;

use App\Entity\Tag;
use App\Entity\User;
use Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdmin;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\InFilter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @TagSonataAdmin(group="User", manager_type="orm", pager_type="simple", icon="fas fa-user")
 */
class UserAdmin extends AbstractAdmin
{
    public function __construct($code, $class = User::class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('ids',
                InFilter::class,
                [
                    'field_name' => 'id',
                    'show_filter' => true,
                    'label' => 'Ids (separated by comma)',
                ]
            )
            ->add('email');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('email')
            ->add('tags', 'list')
            ->add(
                constant(ListMapper::class.'::NAME_ACTIONS') ?: '_action',
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
        $tagAdmin = $this->getConfigurationPool()
            ->getAdminByClass(Tag::class);

        $show
            ->add('id')
            ->add('email')
            ->add('dateOfBirth')
            ->add('roles', 'json')
            ->add('rolesList', 'list')
            ->add('static', 'static', ['virtual_field' => true, 'value' => 'Static value'])
            ->add(
                'tags',
                'grid',
                [
                    'fieldValueOnly' => false,
                    'colspan' => true,
                    'fields' => $tagAdmin->configureGridFields([]),
                ]
            );
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('User')
                ->add('email')
                ->add('plainPassword', TextType::class, ['required' => false])
                ->add(
                    'dateOfBirth',
                    DateTimeType::class,
                    [
                        'widget' => 'single_text',
                        'required' => false,
                        'input' => 'datetime_immutable',
                    ]
                )
                ->add('needChangePassword')
                ->add('manualLock')
            ->end();
    }
}
