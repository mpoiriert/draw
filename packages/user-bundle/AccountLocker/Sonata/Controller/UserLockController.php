<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Sonata\Controller;

use DateTimeImmutable;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class UserLockController extends CRUDController
{
    public function unlockAction(UserLock $userLock): Response
    {
        $this->admin->checkAccess('edit', $userLock);

        $userLock->setUnlockUntil(new DateTimeImmutable('+ 24 hours'));

        try {
            $this->admin->update($userLock);
            $this->addFlash(
                'sonata_flash_success',
                $this->trans('draw_user.account_locker.user_lock.unlock_success', [], 'SonataAdminBundle')
            );
        } catch (\Throwable $error) {
            $this->addFlash(
                'sonata_flash_error',
                $this->trans(
                    'flash_edit_error',
                    ['%name%' => $this->escapeHtml($this->admin->toString($userLock))],
                    'SonataAdminBundle'
                )
            );
        }

        return $this->redirect($this->admin->generateObjectUrl('show', $userLock));
    }
}
