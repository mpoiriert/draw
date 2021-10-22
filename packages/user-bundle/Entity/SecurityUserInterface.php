<?php

namespace Draw\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface SecurityUserInterface extends UserInterface
{
    public function getId();

    public function getPlainPassword(): ?string;

    /**
     * Set the the plain (not encrypted) password to replace the current password upon save.
     *
     * @param string $plainPassword The new password
     */
    public function setPlainPassword(?string $plainPassword);

    public function setPassword(string $password);
}
