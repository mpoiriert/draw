<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction;

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
    public function getActions(array $actions): array;
}
