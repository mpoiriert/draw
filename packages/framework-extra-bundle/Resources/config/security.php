<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
        ->autoconfigure()
        ->autowire()

        ->set('draw.security.role_restricted_authenticator_listener', RoleRestrictedAuthenticatorListener::class)
        ->alias(RoleRestrictedAuthenticatorListener::class, 'draw.security.role_restricted_authenticator_listener');
};
