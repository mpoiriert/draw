<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class RequestPasswordChangeAction extends AbstractController
{
    public function __invoke(AdminInterface $admin, Request $request, ?TranslatorInterface $translator)
    {
        $user = $admin->getSubject();
        $admin->checkAccess('request_password_change', $user);

        $user->setNeedChangePassword(false);
        $user->setNeedChangePassword(true);

        try {
            $admin->update($user);

            $this->addFlash(
                'sonata_flash_success',
                $translator->trans(
                    'draw_user.flash.password_change_requested.success',
                    ['%user%' => $user],
                    'SonataAdminBundle'
                )
            );
        } catch (\Throwable $error) {
            $this->addFlash(
                'sonata_flash_error',
                $error->getMessage()
            );
        }

        return new RedirectResponse($admin->generateObjectUrl('edit', $user));
    }
}
