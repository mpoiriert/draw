<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;

interface PasswordChangeUserInterface extends SecurityUserInterface
{
    public function getNeedChangePassword(): bool;

    public function setNeedChangePassword(bool $needPasswordChange): void;
}
