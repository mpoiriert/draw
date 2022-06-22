<?php

namespace PHPSTORM_META;

override(
    \Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface::getBadge(),
    map(["" => "@"])
);

override(
    \Symfony\Component\Security\Http\Authenticator\Passport\Passport::getBadge(),
    map(["" => "@"])
);
