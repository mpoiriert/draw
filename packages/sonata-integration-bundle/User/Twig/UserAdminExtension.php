<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserAdminExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_admin', [UserAdminRuntime::class, 'getUserAdmin']),
        ];
    }
}
