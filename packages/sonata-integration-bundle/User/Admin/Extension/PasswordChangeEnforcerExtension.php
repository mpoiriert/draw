<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension;

use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use ReflectionClass;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class PasswordChangeEnforcerExtension extends AbstractAdminExtension
{
    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null
    ): array {
        if ($object instanceof PasswordChangeUserInterface && 'edit' === $action) {
            $list['request_password_change'] = [
                'template' => '@DrawSonataIntegration/User/Buttons/request_password_change_button.html.twig',
            ];
        }

        return $list;
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        return [
            'request_password_change' => 'REQUEST_PASSWORD_CHANGE',
        ];
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        if (!(new ReflectionClass($admin->getClass()))->implementsInterface(PasswordChangeUserInterface::class)) {
            return;
        }

        $collection
            ->add(
                'request_password_change',
                $admin->getRouterIdParameter().'/request-password-change',
                [
                    '_controller' => 'draw.sonata.user.action.request_password_change_action',
                ]
            );
    }
}
