<?php

namespace App\Sonata\Admin;

use App\Entity\BaseObject;
use App\Entity\ChildObject1;
use App\Entity\ChildObject2;
use App\Entity\ChildObject3;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ClassFilter;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'model_class' => BaseObject::class,
        'manager_type' => 'orm',
        'group' => 'Object',
    ]
)]
#[AutoconfigureTag(
    name: 'sonata.admin.sub_class',
    attributes: [
        'sub_class' => ChildObject1::class,
        'label' => 'Child Object 1',
    ]
)]
#[AutoconfigureTag(
    name: 'sonata.admin.sub_class',
    attributes: [
        'sub_class' => ChildObject2::class,
        'label' => 'Child Object 2 False Expression',
        'ifExpression' => 'false',
    ]
)]
#[AutoconfigureTag(
    name: 'sonata.admin.sub_class',
    attributes: [
        'sub_class' => ChildObject2::class,
        'label' => 'Child Object 2 True Expression',
        'ifExpression' => 'true',
    ]
)]
#[AutoconfigureTag(
    name: 'sonata.admin.sub_class',
    attributes: [
        'sub_class' => ChildObject3::class,
        'label' => 'Child Object 3',
    ]
)]
class BaseObjectAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'type',
                ClassFilter::class,
                [
                    'sub_classes' => $this->getSubClasses(),
                    'show_filter' => true,
                ]
            )
            ->add(
                'attribute1',
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
                'attribute1',
                fieldDescriptionOptions: [
                    'priority' => 0,
                ]
            )
            ->add('dateTimeImmutable')
            ->add('attribute2')
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $subject = $this->getSubject();

        $form
            ->ifTrue($subject instanceof ChildObject1)
                ->add('attribute1')
                ->add('dateTimeImmutable')
            ->ifEnd()
            ->ifTrue($subject instanceof ChildObject2)
                ->add('attribute2')
            ->ifEnd()
        ;
    }
}
