<?php

namespace App\Sonata\Admin;

use App\Entity\BaseObject;
use App\Entity\ChildObject1;
use App\Entity\ChildObject2;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
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
class BaseObjectAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $subject = $this->getSubject();

        $form
            ->ifTrue($subject instanceof ChildObject1)
                ->add('attribute1')
            ->ifEnd()
            ->ifTrue($subject instanceof ChildObject2)
                ->add('attribute2')
            ->ifEnd();
    }
}
