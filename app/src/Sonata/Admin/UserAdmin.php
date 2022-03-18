<?php

namespace App\Sonata\Admin;

use App\Entity\Tag;
use App\Entity\User;
use Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @TagSonataAdmin(group="User", manager_type="orm", pager_type="simple", icon="fa fa-user")
 */
class UserAdmin extends AbstractAdmin
{
    public function __construct($code, $class = User::class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('email');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('email')
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
            ->add(
                'tags',
                'grid',
                [
                    'fieldValueOnly' => false,
                    'colspan' => true,
                    'fieldsAdmin' => $tagAdmin,
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
                    DateTimePickerType::class,
                    [
                        'required' => false,
                        'input' => 'datetime_immutable',
                    ]
                )
                ->add('needChangePassword')
                ->add('manualLock')

            ->end();
    }
}
