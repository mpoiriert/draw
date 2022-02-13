<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface PasswordChangeUserInterface extends UserInterface
{
    public function getNeedChangePassword(): bool;

    public function setNeedChangePassword(bool $needPasswordChange): void;
}
