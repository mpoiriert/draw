<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;

class PrepareExecutionEvent
{
    public function __construct(
        private ObjectActionExecutioner $objectActionExecutioner,
    ) {
    }

    public function getObjectActionExecutioner(): ObjectActionExecutioner
    {
        return $this->objectActionExecutioner;
    }
}
