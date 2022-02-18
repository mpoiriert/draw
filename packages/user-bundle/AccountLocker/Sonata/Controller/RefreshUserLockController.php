<?php

namespace Draw\Bundle\UserBundle\AccountLocker\Sonata\Controller;

use Draw\Bundle\UserBundle\AccountLocker\AccountLocker;
use Draw\Bundle\UserBundle\AccountLocker\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use RuntimeException;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\EventListener\ConfigureCRUDControllerListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class RefreshUserLockController extends CRUDController
{
    private $accountLocker;

    public function __construct(ContainerInterface $container, AccountLocker $accountLocker)
    {
        $this->accountLocker = $accountLocker;

        if (class_exists(ConfigureCRUDControllerListener::class)) {
            return;
        }

        $this->setContainer($container);

        $this->configureAdmin($container->get('request_stack')->getMasterRequest());
    }

    public function refreshUserLocksAction(): Response
    {
        $request = $this->getRequest();
        $this->assertObjectExists($request, true);

        $existingObject = $this->admin->getSubject();

        $this->admin->checkAccess('refresh-user-locks', $existingObject);

        if (!$existingObject instanceof LockableUserInterface) {
            throw new RuntimeException('Invalid object of class ['.get_class($existingObject).']. It must implements ['.LockableUserInterface::class.']');
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
            ['filter' => ['user' => ['value' => $existingObject->getId()]]]
        ));
    }
}
