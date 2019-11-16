<?php namespace Draw\Bundle\UserBundle\Listener;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EncryptPasswordUserEntityListener
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
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
        if ($user->getPlainPassword()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        }
    }
}