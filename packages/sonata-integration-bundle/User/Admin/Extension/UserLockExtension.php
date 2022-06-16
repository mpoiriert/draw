<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension;

use Draw\Bundle\SonataIntegrationBundle\User\Controller\RefreshUserLockController;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class UserLockExtension extends AbstractAdminExtension
{
    public function getAccessMapping(AdminInterface $admin): array
    {
        return [
            'refresh-user-locks' => 'MASTER',
            'unlock' => 'UNLOCK',
        ];
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        $collection->add(
            'refresh-user-locks',
            $admin->getRouterIdParameter().'/refresh-user-locks',
            ['_controller' => RefreshUserLockController::class.':refreshUserLocksAction']
        );

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
        ?object $object = null
    ): array {
        switch (true) {
            case !$admin->isGranted('refresh-user-locks', $object):
            case !in_array($action, ['edit', 'show']):
                break;
            default:
                $list['refresh-user-lock'] = [
                    'template' => '@DrawSonataIntegration/UserLock/Buttons/refresh_user_locks_button.html.twig',
                ];
        }

        switch (true) {
            case !$admin->isGranted('unlock', $object):
            case !in_array($action, ['edit', 'show']):
                break;
            default:
                $list['unlock'] = [
                    'template' => '@DrawSonataIntegration/User/Buttons/unlock_button.html.twig',
                ];
        }

        return $list;
    }
}
