<?php

namespace Draw\Bundle\UserBundle\Sonata\Controller;

use Draw\Bundle\UserBundle\Entity\TwoFactorAuthenticationTrait;
use Draw\Bundle\UserBundle\Sonata\Form\Enable2fa;
use Draw\Bundle\UserBundle\Sonata\Form\Enable2faForm;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\QrCode\QrCodeGenerator;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends CRUDController
{
    public function enable2faAction(
        Request $request,
        TotpAuthenticatorInterface $totpAuthenticator,
        QrCodeGenerator $qrCodeGenerator
    ) {
        /** @var TwoFactorAuthenticationTrait|TwoFactorInterface $user */
        $user = $this->admin->getSubject();

        $form = $this->createForm(Enable2faForm::class, $enable2fa = new Enable2fa());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setTotpSecret($enable2fa->totpSecret);
            if ($totpAuthenticator->checkCode($user, $enable2fa->code)) {
                $this->admin->getModelManager()->update($user);

                $this->addFlash('sonata_flash_success', '2FA was enabled');

                return $this->redirectTo($user);
            }

            $this->addFlash('sonata_flash_error', 'Invalid code provided. Please, try again.');
        }

        $user->setTotpSecret($totpSecret = $totpAuthenticator->generateSecret());
        $qrCode = $qrCodeGenerator->getTotpQrCode($user);
        $qrCodeSvg = $qrCode->getWriter('svg')->writeString($qrCode);

        if (!$form->isSubmitted()) {
            $enable2fa->totpSecret = $totpSecret;
            $form->setData($enable2fa);
        }

        return new Response(
            $this->renderWithExtraParams(
                '@DrawUser/Sonata/User/enable_2fa.html.twig',
                [
                    'object' => $user,
                    'action' => 'enable-2fa',
                    'form' => $form->createView(),
                    'qrCodeSvg' => $qrCodeSvg,
                ]
            )
        );
    }

    public function disable2faAction(): RedirectResponse
    {
        /** @var TwoFactorAuthenticationTrait $user */
        $user = $this->admin->getSubject();

        $user->setTotpSecret(null);
        $this->admin->getModelManager()->update($user);

        $this->addFlash('sonata_flash_success', '2FA was disabled');

        return $this->redirectTo($user);
    }
}
