<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Extension;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\AdminAction\DeleteAdminAction;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;

class DefaultActionExtension extends AbstractAdminExtension implements ActionableAdminExtensionInterface
{
    private const SUPPORTED_ACTIONS = [
        'delete' => DeleteAdminAction::class,
    ];

    private array $actions;

    public function __construct(
        array $actions = [],
    ) {
        $this->actions = array_values(
            array_intersect(
                $actions,
                array_keys(self::SUPPORTED_ACTIONS)
            )
        );
    }

    public function getActions(array $actions): array
    {
        foreach ($this->actions as $action) {
            if (\array_key_exists($action, $actions)) {
                continue;
            }

            $actionClass = self::SUPPORTED_ACTIONS[$action];

            $actions[$action] = new $actionClass();
        }

        return $actions;
    }
}
