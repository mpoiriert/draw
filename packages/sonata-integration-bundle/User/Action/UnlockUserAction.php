<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Action;

use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class UnlockUserAction
{
    private UserFeedInterface $userFeed;

    public function __construct(UserFeedInterface $userFeed)
    {
        $this->userFeed = $userFeed;
    }

    public function __invoke(AdminInterface $admin, UserInterface $currentUser)
    {
        /** @var LockableUserInterface $user */
        $user = $admin->getSubject();

        $admin->checkAccess('unlock', $user);

        $user->temporaryUnlockAll(new \DateTimeImmutable('+ 24 hours'));

        try {
            $admin->update($user);

            $this->userFeed
                ->addToFeed(
                    $currentUser,
                    'sonata_flash_success',
                    'draw_user.account_locker.user_lock.unlock_success',
                    [],
                    'SonataAdminBundle'
                );
        } catch (\Throwable $error) {
            $this->userFeed
                ->addToFeed(
                    $currentUser,
                    'sonata_flash_error',
                    'flash_edit_error',
                    ['%name%' => htmlspecialchars((string) $user)],
                    'SonataAdminBundle',
                );
        }

        return new RedirectResponse($admin->generateObjectUrl('show', $user));
    }
}
