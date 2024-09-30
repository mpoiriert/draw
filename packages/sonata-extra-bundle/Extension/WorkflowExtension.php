<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction\WorkflowTransitionAdminAction;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension\ActionableAdminExtensionInterface;
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
        return $actions + ['workflow_apply_transition' => new WorkflowTransitionAdminAction()];
    }
}
