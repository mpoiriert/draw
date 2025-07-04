<?php

namespace App\Sonata\Admin;

use App\Controller\Admin\AddRolesAminAction;
use App\Controller\Admin\MakeAdminAction;
use App\Controller\Admin\SetPreferredLocaleAction;
use App\Entity\Tag;
use App\Entity\User;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ActionableAdminInterface;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\InFilter;
use Draw\Bundle\SonataExtraBundle\Form\Extension\Core\Type\SingleLineDateTimeType;
use Draw\Bundle\SonataExtraBundle\ListPriorityAwareAdminInterface;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\UserLockAdmin;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Translation\TranslatableMessage;

#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'model_class' => User::class,
        'manager_type' => 'orm',
        'group' => 'User',
        'icon' => 'fas fa-user',
        'pager_type' => 'simple',
    ]
)]
class UserAdmin extends AbstractAdmin implements ListPriorityAwareAdminInterface, ActionableAdminInterface
{
    public function getListFieldPriorityOptions(): array
    {
        return [
            'defaultMaxField' => 8,
            'defaultFieldPriorities' => [
                'childObject1' => 0,
                'childObject2' => -1,
            ],
        ];
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'ids',
                InFilter::class,
                [
                    'field_name' => 'id',
                    'show_filter' => true,
                    'label' => 'Ids (separated by comma)',
                ]
            )
            ->add('email')
            ->add(
                'tags',
                null,
                [
                    'field_options' => [
                        'multiple' => true,
                    ],
                ]
            )
            ->add(
                'userTags.tag'
            )
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('email')
            ->add('childObject1')
            ->add('childObject2')
            ->add('userTags', 'list')
            ->add('tags')
            ->add('roles', 'list')
            ->add('isLocked', 'boolean', ['inverse' => true])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var TagAdmin $tagAdmin */
        $tagAdmin = $this->getConfigurationPool()
            ->getAdminByClass(Tag::class)
        ;

        /** @var UserLockAdmin $userLockAdmin */
        $userLockAdmin = $this->getConfigurationPool()
            ->getAdminByClass(UserLock::class)
        ;

        $show
            ->add('id')
            ->add('childObject1')
            ->add('childObject2')
            ->add('email')
            ->add('dateOfBirth')
            ->add('preferredLocale')
            ->add('roles', 'json')
            ->add('rolesList', 'list')
            ->add('static', 'static', ['virtual_field' => true, 'value' => 'Static value'])
            ->add('tags')
            ->add('isLocked', 'boolean', ['inverse' => true])
            ->add(
                'userLocks',
                'grid',
                [
                    'fieldValueOnly' => false,
                    'colspan' => true,
                    'fields' => $userLockAdmin->configureGridFields([]),
                ]
            )
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('User')
                ->with('General', ['class' => 'col-sm-6'])
                    ->add('email')
                    ->add('tags')
                    ->add(
                        'userTags',
                        CollectionType::class,
                        [
                            'required' => false,
                            'by_reference' => false,
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                        ]
                    )
                    ->add('childObject1')
                    ->add('childObject2')
                    ->add('plainPassword', TextType::class, ['required' => false])
                    ->add(
                        'dateOfBirth',
                        SingleLineDateTimeType::class,
                        [
                            'required' => false,
                        ]
                    )
                    ->add('needChangePassword')
                    ->add('manualLock')
                    ->add(
                        'isLocked',
                        CheckboxType::class,
                        ['disabled' => true, 'required' => false]
                    )
                    ->add(
                        'twoFactorAuthenticationEnabledProviders',
                        ChoiceType::class,
                        [
                            'choices' => ['email' => 'email', 'totp' => 'totp'],
                            'choice_label' => static fn ($choice) => new TranslatableMessage('enabledProviders.choice.'.$choice),
                            'multiple' => true,
                            'expanded' => true,
                        ]
                    )
                    ->add(
                        'userLocks',
                        CollectionType::class,
                        [
                            'required' => false,
                            'by_reference' => false,
                            'type_options' => [
                                'delete' => false,
                            ],
                        ],
                        [
                            'edit' => 'inline',
                            'inline' => 'table',
                        ]
                    )
                ->end()
                ->with('Security', ['class' => 'col-sm-6'])
                    ->add(
                        'roles',
                        ChoiceType::class,
                        [
                            'choices' => ['ROLE_USER' => 'ROLE_USER', 'ROLE_ADMIN' => 'ROLE_ADMIN'],
                            'multiple' => true,
                            'expanded' => true,
                        ]
                    )
                ->end()
            ->end()
        ;
    }

    public function getActions(): array
    {
        return [
            'makeAdmin' => (new AdminAction('makeAdmin', true))
                ->setController(MakeAdminAction::class)
                ->setIcon('fa fa-user-plus')
                ->setBatchController(MakeAdminAction::class),
            'addRoles' => (new AdminAction('addRoles', true))
                ->setController(AddRolesAminAction::class)
                ->setBatchController(AddRolesAminAction::class),
            'setPreferredLocale' => (new AdminAction('setPreferredLocale', true))
                ->setIcon('fa fa-language')
                ->setController(SetPreferredLocaleAction::class)
                ->setRoutePattern(
                    \sprintf(
                        '%s/preferred-locale/{_locale}',
                        $this->getRouterIdParameter(),
                    )
                )
                ->setActionsCallback(
                    static function (AdminAction $action): iterable {
                        foreach (['en', 'fr'] as $locale) {
                            yield (clone $action)
                                ->setLabel(strtoupper($locale))
                                ->setRouteParameters(['_locale' => $locale])
                            ;
                        }
                    }
                ),
        ];
    }
}
