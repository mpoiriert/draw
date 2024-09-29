<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class UnlockUserLockExtension extends AbstractAdminExtension
{
    public function getAccessMapping(AdminInterface $admin): array
    {
        return [
            'unlock' => 'UNLOCK',
        ];
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        $collection->add(
            'unlock',
            $admin->getRouterIdParameter().'/unlock',
            ['_controller' => 'draw.sonata.user.action.unlock_user_action']
        );
    }

    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null,
    ): array {
        switch (true) {
            case !$object instanceof LockableUserInterface:
            case !$object->isLocked():
            case !$admin->isGranted('unlock', $object):
            case !\in_array($action, ['edit', 'show'], true):
                break;
            default:
                $list['unlock'] = [
                    'template' => '@DrawSonataIntegration/User/Buttons/unlock_button.html.twig',
                ];
        }

        return $list;
    }
}
