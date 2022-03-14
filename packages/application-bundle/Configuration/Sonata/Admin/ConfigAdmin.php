<?php

namespace Draw\Bundle\ApplicationBundle\Configuration\Sonata\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ConfigAdmin extends AbstractAdmin
{
    public function __construct($code, $class, $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->setTranslationDomain('DrawApplicationBundleSonata');
    }

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
            function ($data) {
                return null !== $data ? json_encode($data, \JSON_PRETTY_PRINT) : null;
            },
            function ($data) {
                return !empty($data) ? json_decode($data, true) : null;
            }
        ));
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');
    }
}
