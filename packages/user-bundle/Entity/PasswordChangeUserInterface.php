<?php

namespace Draw\Bundle\UserBundle\Entity;

interface PasswordChangeUserInterface extends SecurityUserInterface
{
    public function requestPasswordChange(): void;

    public function getNeedChangePassword(): bool;

    public function setNeedChangePassword(bool $needPasswordChange): static;
}
