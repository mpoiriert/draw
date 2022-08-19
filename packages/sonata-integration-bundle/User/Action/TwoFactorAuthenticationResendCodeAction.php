<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Action;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorAuthenticationResendCodeAction
{
    private CodeGeneratorInterface $codeGenerator;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        CodeGeneratorInterface $codeGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(UserInterface $user): RedirectResponse
    {
        if (!$user instanceof TwoFactorInterface) {
            throw new \LogicException('User must implement class '.TwoFactorInterface::class);
        }

        $this->codeGenerator->generateAndSend($user);

        return new RedirectResponse($this->urlGenerator->generate('admin_2fa_login', ['preferProvider' => 'email']));
    }
}
