<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension\ActionableAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

interface ActionableAdminInterface extends AdminInterface
{
    /**
     * List of AdminAction available for this admin.
     *
     * Key is the action name.
     *
     * @see ActionableAdminExtension
     *
     * @return array<string,AdminAction>
     */
    public function getActions(): array;
}
