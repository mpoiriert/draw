<?php

namespace Draw\Bundle\CommandBundle\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SystemToken extends AbstractToken
{
    public function __construct(array $roles = [])
    {
        parent::__construct($roles);
        $this->setUser('system');
        $this->setAuthenticated(true);
    }

    public function getCredentials()
    {
        return null;
    }
}
