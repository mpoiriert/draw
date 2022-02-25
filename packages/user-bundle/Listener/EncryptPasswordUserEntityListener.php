<?php

namespace Draw\Bundle\UserBundle\Listener;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EncryptPasswordUserEntityListener
{
    private $passwordHasher;
    private $autoGeneratePassword;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        $autoGeneratePassword = true
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->autoGeneratePassword = $autoGeneratePassword;
    }

    public function preUpdate(SecurityUserInterface $user)
    {
        $this->updatePassword($user);
    }

    public function prePersist(SecurityUserInterface $user)
    {
        $this->updatePassword($user);
    }

    public function postPersist(SecurityUserInterface $user)
    {
        $user->setPlainPassword(null);
    }

    public function postUpdate(SecurityUserInterface $user)
    {
        $user->setPlainPassword(null);
    }

    private function updatePassword(SecurityUserInterface $user)
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
