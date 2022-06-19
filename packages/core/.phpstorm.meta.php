<?php namespace PHPSTORM_META {
    override(
        \Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface::getBadge(0),
        map(["" => "@"])
    );

    override(
        \Symfony\Component\Security\Http\Authenticator\Passport\Passport::getBadge(0),
        map(["" => "@"])
    );
}

