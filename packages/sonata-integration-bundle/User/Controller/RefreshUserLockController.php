<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Controller;

use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshUserLockController extends CRUDController
{
    public function __construct(private AccountLocker $accountLocker)
    {
    }

    public function refreshUserLocksAction(Request $request): Response
    {
        $this->assertObjectExists($request, true);

        $existingObject = $this->admin->getSubject();

        $this->admin->checkAccess('refresh-user-locks', $existingObject);

        if (!$existingObject instanceof LockableUserInterface) {
            throw new \RuntimeException('Invalid object of class ['.$existingObject::class.']. It must implements ['.LockableUserInterface::class.']');
        }

        $this->accountLocker->refreshUserLocks($existingObject);

        $this->admin->update($existingObject);
        $this->addFlash(
            'sonata_flash_success',
            $this->trans('draw_user.account_locker.user_lock.refresh_success', [], 'SonataAdminBundle')
        );

        $admin = $this->admin->getConfigurationPool()->getAdminByClass(UserLock::class);

        return $this->redirect($admin->generateUrl(
            'list',
            ['filter' => ['user' => ['value' => $admin->id($existingObject)]]]
        ));
    }
}
