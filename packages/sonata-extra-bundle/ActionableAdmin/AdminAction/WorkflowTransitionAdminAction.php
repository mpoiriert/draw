<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Action\WorkflowTransitionAction;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;

class WorkflowTransitionAdminAction extends AdminAction
{
    public function __construct()
    {
        parent::__construct('workflow_apply_transition', true);

        $this
            ->setController(WorkflowTransitionAction::class)
            ->clearForActions()
            ->setForEntityListAction(false)
        ;
    }
}
