<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction\DeleteAdminAction;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('sonata.admin.extension', attributes: ['global' => true])]
class DefaultActionExtension extends AbstractAdminExtension implements ActionableAdminExtensionInterface
{
    public function getActions(array $actions): array
    {
        if (\array_key_exists('delete', $actions)) {
            return $actions;
        }

        $actions['delete'] = new DeleteAdminAction();

        return $actions;
    }
}
