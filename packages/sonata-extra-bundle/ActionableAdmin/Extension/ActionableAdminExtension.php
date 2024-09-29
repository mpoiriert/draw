<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminActionLoader;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(
    'sonata.admin.extension',
    attributes: [
        'priority' => -1000,
        'global' => true,
    ]
)]
class ActionableAdminExtension extends AbstractAdminExtension
{
    public function __construct(
        private AdminActionLoader $actionLoader,
    ) {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        foreach ($this->actionLoader->getActions($admin) as $action) {
            $defaults = [
                '_actionableAdmin' => [
                    'action' => $action->getName(),
                ],
            ];

            if ($action->getController()) {
                $defaults['_controller'] = $action->getController();
            }

            $pattern = $action->getTargetEntity()
                ? $admin->getRouterIdParameter().'/'.$action->getUrlSuffix()
                : $action->getUrlSuffix();

            $collection
                ->add(
                    $action->getName(),
                    $pattern,
                    defaults: $defaults
                )
            ;
        }
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        $accessMapping = [];

        foreach ($this->actionLoader->getActions($admin) as $adminAction) {
            $accessMapping[$adminAction->getName()] = $adminAction->getAccess();
        }

        return $accessMapping;
    }

    public function configureBatchActions(AdminInterface $admin, array $actions): array
    {
        foreach ($this->actionLoader->getActions($admin) as $adminAction) {
            if (!$batchController = $adminAction->getBatchController()) {
                continue;
            }

            if (!$admin->hasAccess($adminAction->getName())) {
                continue;
            }

            $actions[$adminAction->getName()] = [
                'label' => $adminAction->getLabel(),
                'translation_domain' => $adminAction->getTranslationDomain(),
                'controller' => $batchController,
            ];
        }

        return $actions;
    }

    public function configureListFields(ListMapper $list): void
    {
        foreach ($this->actionLoader->getActions($list->getAdmin()) as $adminAction) {
            if (!$adminAction->getForEntityListAction()) {
                continue;
            }

            if (!$list->has(ListMapper::NAME_ACTIONS)) {
                $list->add(
                    ListMapper::NAME_ACTIONS,
                    ListMapper::TYPE_ACTIONS,
                    [
                        'label' => 'Action',
                    ]
                );
            }

            $actionFieldDescription = $list->get(ListMapper::NAME_ACTIONS);

            $actions = $actionFieldDescription->getOption('actions');

            $actions[$adminAction->getName()] = [
                'template' => '@DrawSonataExtra/Action/list_action.html.twig',
                'action' => $adminAction,
            ];

            $actionFieldDescription->setOption('actions', $actions);
        }
    }

    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null,
    ): array {
        foreach ($this->actionLoader->getActions($admin) as $adminAction) {
            if (!$adminAction->isForAction($action)) {
                continue;
            }

            // This is creation flow we never add actions in that flow
            if (null !== $object && null === $admin->id($object)) {
                continue;
            }

            if ($adminAction->getTargetEntity() && null === $object) {
                continue;
            }

            if (!$admin->hasAccess($adminAction->getName(), $object)) {
                continue;
            }

            $list[$adminAction->getName()] = [
                'template' => '@DrawSonataExtra/Action/action.html.twig',
                'action' => $adminAction,
            ];
        }

        return $list;
    }
}
