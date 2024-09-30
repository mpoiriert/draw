<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension\ActionableAdminExtensionInterface;
use Draw\Bundle\SonataExtraBundle\Controller\WorkflowTransitionAction;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class WorkflowExtension extends \Yokai\SonataWorkflow\Admin\Extension\WorkflowExtension implements ActionableAdminExtensionInterface
{
    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        // Prevent the original implementation from being called
    }

    public function getActions(array $actions): array
    {
        $actions['workflow_apply_transition'] = (new AdminAction(
            'workflow_apply_transition',
            true,
        ))
            ->setController(WorkflowTransitionAction::class)
            ->clearForActions()
            ->setForEntityListAction(false)
        ;

        return $actions;
    }
}
