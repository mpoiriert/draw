<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// todo check why we have this with a $autoGeneratePassword parameter, should it not be always true ?
class EncryptPasswordUserEntityListener
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private bool $autoGeneratePassword = true
    ) {
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
