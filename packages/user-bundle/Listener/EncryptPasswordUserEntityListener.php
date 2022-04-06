<?php

namespace Draw\Bundle\UserBundle\Listener;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// todo check why we have this with a $autoGeneratePassword parameter, should it not be always true ?
class EncryptPasswordUserEntityListener
{
    private UserPasswordHasherInterface $passwordHasher;
    private bool $autoGeneratePassword;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        bool $autoGeneratePassword = true
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->autoGeneratePassword = $autoGeneratePassword;
    }

    public function preUpdate(SecurityUserInterface $user): void
    {
        $this->updatePassword($user);
    }

    public function prePersist(SecurityUserInterface $user): void
    {
        $this->updatePassword($user);
    }

    public function postPersist(SecurityUserInterface $user): void
    {
        $user->setPlainPassword(null);
    }

    public function postUpdate(SecurityUserInterface $user): void
    {
        $user->setPlainPassword(null);
    }

    private function updatePassword(SecurityUserInterface $user): void
    {
        switch (true) {
            case $user->getPlainPassword():
            case $user->getPassword():
            case !$this->autoGeneratePassword:
            case $user instanceof PasswordChangeUserInterface && $user->getNeedChangePassword():
                break;
            default:
                $user->setPlainPassword(uniqid());
                break;
        }

        if ($user->getPlainPassword()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
        }
    }
}
