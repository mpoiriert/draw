<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

interface ByEmailInterface extends TwoFactorInterface, TwoFactorAuthenticationUserInterface
{
}
