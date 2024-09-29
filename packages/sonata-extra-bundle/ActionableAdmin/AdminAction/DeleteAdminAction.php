<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Action\DeleteAction;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;

class DeleteAdminAction extends AdminAction
{
    public function __construct()
    {
        parent::__construct('delete', true);

        $this
            ->setLabel('execution.action.delete.label')
            ->setIcon('fas fa-times')
            ->setController(DeleteAction::class)
            ->setTranslationDomain('DrawSonataExtraBundle')
        ;
    }
}
