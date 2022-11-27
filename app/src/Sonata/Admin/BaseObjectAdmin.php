<?php

namespace App\Sonata\Admin;

use App\Entity\BaseObject;
use App\Entity\ChildObject1;
use App\Entity\ChildObject2;
use Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdmin;
use Draw\Bundle\SonataExtraBundle\Annotation\TagSonataAdminSubClass;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * @TagSonataAdmin(
 *     group="Object",
 *     manager_type="orm",
 *     model_class=BaseObject::class
 * )
 *
 * @TagSonataAdminSubClass(
 *     label="Child Object 1",
 *     sub_class=ChildObject1::class
 * )
 * @TagSonataAdminSubClass(
 *     label="Child Object 2 False expresion",
 *     sub_class=ChildObject2::class,
 *     ifExpression="false"
 * )
 * @TagSonataAdminSubClass(
 *     label="Child Object 2 True Expression",
 *     sub_class=ChildObject2::class,
 *     ifExpression="true"
 * )
 */
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
