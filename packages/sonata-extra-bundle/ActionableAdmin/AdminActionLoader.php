<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension\ActionableAdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class AdminActionLoader
{
    private array $actions = [];

    /**
     * @return array<string,AdminAction>
     */
    public function getActions(AdminInterface $admin): array
    {
        if (!\array_key_exists($admin->getCode(), $this->actions)) {
            $actions = $admin instanceof ActionableAdminInterface
                ? $admin->getActions()
                : [];

            foreach ($admin->getExtensions() as $extension) {
                if (!$extension instanceof ActionableAdminExtensionInterface) {
                    continue;
                }

                $actions = $extension->getActions($actions);
            }

            array_walk(
                $actions,
                function (AdminAction $adminAction) use ($admin): void {
                    // Set default translation domain
                    $adminAction->setTranslationDomain($adminAction->getTranslationDomain() ?? $admin->getTranslationDomain());
                }
            );

            $this->actions[$admin->getCode()] = $actions;
        }

        return $this->actions[$admin->getCode()];
    }
}
