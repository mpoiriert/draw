<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Admin;

use Draw\Bundle\SonataIntegrationBundle\User\Controller\UserLockUnlockController;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class UserLockAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add(
            'user',
            ModelFilter::class,
            [
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => 'email',
                ],
            ]
        );
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->add('reason')
            ->add('user')
            ->add('createdAt')
            ->add('lockOn')
            ->add('expiresAt')
            ->add('unlockUntil')
            ->add('active', 'boolean', ['inverse' => true])
            ->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                    ],
                ]
            );
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form->add(
            'unlockUntil',
            DateTimeType::class,
            [
                'required' => false,
                'widget' => 'single_text',
                'format' => DateTimeType::HTML5_FORMAT,
            ]
        );
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('user')
            ->add('reason')
            ->add('createdAt')
            ->add('lockOn')
            ->add('expiresAt')
            ->add('unlockUntil')
            ->add('active', 'boolean', ['inverse' => true])
            ->add(
                'explanation',
                null,
                [
                    'template' => '@DrawSonataIntegration/UserLock/CRUD/show_reason_details.html.twig',
                ]
            );
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit', 'show']);
        $collection->add(
            'unlock',
            $this->getRouterIdParameter().'/unlock',
            ['_controller' => UserLockUnlockController::class.':unlockAction']
        );
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        switch ($action) {
            case 'show':
            case 'edit':
                if ($this->isGranted('EDIT', $object)) {
                    $buttonList['unlock'] = [
                        'template' => '@DrawSonataIntegration/UserLock/Buttons/unlock_button.html.twig',
                    ];
                }
                break;
        }

        return $buttonList;
    }
}
