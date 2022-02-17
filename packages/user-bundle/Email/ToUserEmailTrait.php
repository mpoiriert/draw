<?php

namespace Draw\Bundle\UserBundle\Email;

trait ToUserEmailTrait
{
    private $userIdentifier;

    public function setUserIdentifier($userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }
}
