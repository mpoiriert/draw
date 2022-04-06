<?php

namespace Draw\Bundle\UserBundle\Email;

trait ToUserEmailTrait
{
    private ?string $userIdentifier = null;

    public function setUserIdentifier(string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }
}
