<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Sonata\AdminBundle\Admin\AdminInterface;

interface ActionableInterface extends AdminInterface
{
    /**
     * @return iterable<AdminAction>
     */
    public function getActions(): iterable;
}
