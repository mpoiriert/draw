<?php

namespace Draw\Bundle\SonataIntegrationBundle\Configuration\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ConfigAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name');
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id')
            ->add(
                'data',
                TextareaType::class,
                [
                    'attr' => ['rows' => 20],
                ]
            );

        $form->get('data')->addModelTransformer(new CallbackTransformer(
            fn ($data) => null !== $data ? json_encode($data, \JSON_PRETTY_PRINT) : null,
            fn ($data) => !empty($data) ? json_decode((string) $data, true, 512, \JSON_THROW_ON_ERROR) : null
        ));
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');
    }
}
