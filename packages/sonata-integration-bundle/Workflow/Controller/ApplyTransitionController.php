<?php

namespace Draw\Bundle\SonataIntegrationBundle\Workflow\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Yokai\SonataWorkflow\Controller\WorkflowControllerTrait;

class ApplyTransitionController extends CRUDController
{
    use WorkflowControllerTrait;
}