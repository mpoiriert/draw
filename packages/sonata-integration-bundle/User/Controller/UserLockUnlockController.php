<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Controller;

use DateTimeImmutable;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserLockUnlockController extends CRUDController
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
        } catch (Throwable $error) {
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
