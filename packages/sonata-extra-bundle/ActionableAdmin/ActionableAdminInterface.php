<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension\ActionableAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @template T of object
 *
 * @template-extends AdminInterface<T>
 */
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
