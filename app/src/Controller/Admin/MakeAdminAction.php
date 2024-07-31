<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\BatchActionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class MakeAdminAction implements BatchActionInterface
{
    public function __invoke(UserAdmin $admin, User $user): Response
    {
        $user->setRoles(
            array_values(
                array_unique(
                    array_merge(
                        $user->getRoles(),
                        ['ROLE_ADMIN']
                    )
                )
            )
        );

        $admin->update($user);

        return new RedirectResponse($admin->generateUrl('list'));
    }

    public function getBatchCallable(): callable
    {
        return [$this, '__invoke'];
    }
}
