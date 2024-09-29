<?php

namespace App\Sonata\Admin;

use App\Entity\UserTag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'model_class' => UserTag::class,
        'manager_type' => 'orm',
        'show_in_dashboard' => false,
    ]
)]
class UserTagAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('tag')
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('edit')
            ->remove('show')
        ;
    }
}
