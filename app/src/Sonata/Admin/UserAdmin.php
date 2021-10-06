<?php

namespace App\Sonata\Admin;

use Draw\Bundle\UserBundle\Sonata\Controller\UserController;
use KunicMarko\SonataAutoConfigureBundle\Annotation\AdminOptions;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @AdminOptions(
 *     group="User",
 *     pagerType="simple",
 *     icon="<i class='fa fa-user'></i>",
 *     controller=UserController::class
 * )
 */
class UserAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('email')
            ->add(
                '_action',
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

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->tab('User')
                ->add('email')
                ->add('plainPassword', TextType::class, ['required' => false])
            ->end();
    }
}
