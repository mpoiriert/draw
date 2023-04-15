<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Controller;

use Draw\Bundle\SonataIntegrationBundle\User\Form\Enable2fa;
use Draw\Bundle\SonataIntegrationBundle\User\Form\Enable2faForm;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByTimeBaseOneTimePasswordInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\QrCode\QrCodeGenerator;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthenticationController extends CRUDController
{
    protected function loadUser(Request $request): ByTimeBaseOneTimePasswordInterface
    {
        $user = $this->admin->getSubject();
        if (!$user instanceof ByTimeBaseOneTimePasswordInterface) {
            throw new \RuntimeException('User not found');
        }

        return $user;
    }

    public function enable2faAction(
        Request $request,
        TotpAuthenticatorInterface $totpAuthenticator,
        QrCodeGenerator $qrCodeGenerator
    ): Response {
        $user = $this->loadUser($request);

        $this->admin->checkAccess('enable-2fa', $user);

        $form = $this->createForm(
            Enable2faForm::class,
            $enable2fa = new Enable2fa(),
            ['user' => $user]
        );

        \assert($form instanceof Form);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ('cancel' === $form->getClickedButton()?->getName()) {
                return new RedirectResponse($this->admin->generateObjectUrl('disable-2fa', $user));
            }

            $user->setTotpSecret($enable2fa->totpSecret);
            if ($totpAuthenticator->checkCode($user, $enable2fa->code)) {
                $user->enableTwoFActorAuthenticationProvider('totp');
                $this->admin->getModelManager()->update($user);

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans('admin.flash.2fa_enabled', [], 'DrawUserBundle')
                );

                return $this->redirectTo($request, $user);
            }

            $this->addFlash(
                'sonata_flash_error',
                $this->trans('admin.flash.2fa_invalid_code', [], 'DrawUserBundle')
            );
        }

        // If the form was submitted it will be assigned, we want to keep the same one
        // Otherwise generate one
        if (!$form->isSubmitted()) {
            $user->setTotpSecret($totpAuthenticator->generateSecret());
            $totpSecret = $user->getTotpSecret();
            $enable2fa->totpSecret = $totpSecret;
            $form->setData($enable2fa);
        }

        $qrCode = $qrCodeGenerator->getTotpQrCode($user);

        return new Response(
            $this->renderWithExtraParams(
                '@DrawSonataIntegration/User/enable_2fa.html.twig',
                [
                    'object' => $user,
                    'action' => 'enable-2fa',
                    'form' => $form->createView(),
                    'qrCodeSvg' => $qrCode->getWriter('svg')->writeString($qrCode),
                    'totpSecret' => $user->getTotpSecret(),
                ]
            )
        );
    }

    public function disable2faAction(Request $request): RedirectResponse
    {
        $user = $this->loadUser($request);

        $this->admin->checkAccess('disable-2fa', $user);

        $user->setTotpSecret(null);
        $user->disableTwoFActorAuthenticationProvider('totp');
        $this->admin->getModelManager()->update($user);
        $this->addFlash(
            'sonata_flash_success',
            $this->trans('admin.flash.2fa_disabled', [], 'DrawUserBundle')
        );

        return $this->redirectTo($request, $user);
    }
}
