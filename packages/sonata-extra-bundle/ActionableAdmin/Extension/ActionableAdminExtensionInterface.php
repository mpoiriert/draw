<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * Interface to use on you ActionExtension to add actions to an Admin via an extension.
 *
 * @see ActionableAdminExtension
 */
interface ActionableAdminExtensionInterface
{
    /**
     * @param array<string,AdminAction> $actions
     *
     * @return array<string,AdminAction>
     */
    public function getActions(AdminInterface $admin, array $actions): array;
}
