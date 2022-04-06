<?php namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \Symfony\Component\Security\Http\Authenticator\Passport\Passport::getBadge() => [
            "" == "@",
        ],
        \Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface::getBadge() => [
            "" == "@",
        ],
        \Symfony\Component\Security\Http\Event\CheckPassportEvent::getPassport() => [
            "" == '\Symfony\Component\Security\Http\Authenticator\Passport\Passport\Passport',
        ],
    ];
}