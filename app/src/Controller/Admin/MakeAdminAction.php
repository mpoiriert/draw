<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\BatchIterator;
use Draw\Bundle\SonataExtraBundle\Notifier\Notification\SonataNotification;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;

class MakeAdminAction
{
    public function __invoke(UserAdmin $admin, User $user, NotifierInterface $notifier): Response
    {
        if ($this->addAdminRole($user)) {
            $admin->update($user);
            $notifier->send(SonataNotification::success('User is now an admin'));
        } else {
            $notifier->send(
                (new SonataNotification('User already has the admin role'))
                    ->setSonataFlashType('info')
            );
        }

        return new RedirectResponse($admin->generateObjectUrl('show', $user));
    }

    private function addAdminRole(User $user): bool
    {
        $currentRoles = $user->getRoles();

        if (\in_array('ROLE_ADMIN', $currentRoles)) {
            return false;
        }

        $user->setRoles([
            ...$currentRoles,
            'ROLE_ADMIN',
        ]);

        return true;
    }

    /**
     * @param BatchIterator<User> $batchIterator
     */
    public function batch(BatchIterator $batchIterator, AdminInterface $admin): Response
    {
        foreach ($batchIterator->getObjects() as $object) {
            if (!$this->addAdminRole($object)) {
                $batchIterator->skip('already-admin');

                continue;
            }

            $admin->update($object);
        }

        return new RedirectResponse($admin->generateUrl('list'));
    }
}
