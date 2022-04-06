<?php

namespace Draw\Bundle\UserBundle\Sonata\Security;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\RoleRestrictedBadge;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;

class AdminLoginAuthenticator extends FormLoginAuthenticator
{
    private string $requiredRole;

    public function __construct(
        HttpUtils $httpUtils,
        UserProviderInterface $userProvider,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options
    ) {
        parent::__construct($httpUtils, $userProvider, $successHandler, $failureHandler, $options);

        $this->requiredRole = $options['required_role'] ?? 'ROLE_SONATA_ADMIN';
    }

    public function authenticate(Request $request): Passport
    {
        return parent::authenticate($request)->addBadge(new RoleRestrictedBadge($this->requiredRole));
    }
}
