<?php

namespace Draw\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface SecurityUserInterface extends PasswordAuthenticatedUserInterface, UserInterface
{
    public function getId();

    public function getPlainPassword(): ?string;

    /**
     * Set the the plain (not encrypted) password to replace the current password upon save.
     *
     * @param ?string $plainPassword The new password
     */
    public function setPlainPassword(?string $plainPassword): void;

    public function setPassword(?string $password): void;
}
