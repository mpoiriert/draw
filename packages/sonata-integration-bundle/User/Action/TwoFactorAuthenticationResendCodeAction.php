<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Action;

use Draw\Component\Security\Core\Security;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwoFactorAuthenticationResendCodeAction
{
    public function __construct(
        private CodeGeneratorInterface $codeGenerator,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Security $security): RedirectResponse
    {
        $user = $security->getUser();

        if (!$user instanceof TwoFactorInterface) {
            throw new \LogicException('User must implement class '.TwoFactorInterface::class);
        }

        $this->codeGenerator->generateAndSend($user);

        return new RedirectResponse($this->urlGenerator->generate('admin_2fa_login', ['preferProvider' => 'email']));
    }
}
