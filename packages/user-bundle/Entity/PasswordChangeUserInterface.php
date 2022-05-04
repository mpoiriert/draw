<?php

namespace Draw\Bundle\UserBundle\Entity;

interface PasswordChangeUserInterface extends SecurityUserInterface
{
    public function getNeedChangePassword(): bool;

    public function setNeedChangePassword(bool $needPasswordChange): void;
}
