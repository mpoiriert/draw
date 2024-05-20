<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\ListMapper;

class AutoActionExtension extends AbstractAdminExtension
{
    public const DEFAULT_ACTIONS = [
        'show' => [],
        'edit' => [],
        'delete' => [],
    ];

    public function __construct(
        private array $actions = self::DEFAULT_ACTIONS,
        private array $ignoreAdmins = []
    ) {
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function configureListFields(ListMapper $list): void
    {
        if ($list->has(ListMapper::NAME_ACTIONS)) {
            return;
        }

        $admin = $list->getAdmin();

        foreach ($this->ignoreAdmins as $ignoreAdmin) {
            if ($admin instanceof $ignoreAdmin) {
                return;
            }
        }

        $actions = [];
        $routes = $admin->getRoutes();

        foreach ($this->actions as $action => $options) {
            if ($routes->has($routes->getCode($action))) {
                $actions[$action] = $options;
            }
        }

        if (!empty($actions)) {
            $list->add(
                ListMapper::NAME_ACTIONS,
                ListMapper::TYPE_ACTIONS,
                [
                    'label' => 'Action',
                    'actions' => $actions,
                ]
            );
        }
    }
}
