<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Action;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorAuthenticationResendCodeAction
{
    public function __invoke(CodeGenerator $codeGenerator, Request $request, UserInterface $user): RedirectResponse
    {
        if (!$user instanceof TwoFactorInterface) {
            throw new \LogicException('User must implement class '.TwoFactorInterface::class);
        }

        $codeGenerator->generateAndSend($user);

        return new RedirectResponse($request->headers->get('Referer'));
    }
}
