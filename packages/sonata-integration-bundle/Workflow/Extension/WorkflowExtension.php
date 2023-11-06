<?php

namespace Draw\Bundle\SonataIntegrationBundle\Workflow\Extension;

use Draw\Bundle\SonataIntegrationBundle\Workflow\Controller\ApplyTransitionController;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class WorkflowExtension extends \Yokai\SonataWorkflow\Admin\Extension\WorkflowExtension
{
    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        $collection->add(
            'workflow_apply_transition',
            $admin->getRouterIdParameter() . '/workflow/transition/{transition}/apply',
            defaults: [
                '_controller' => ApplyTransitionController::class . '::workflowApplyTransitionAction',
            ]
        );
    }
}