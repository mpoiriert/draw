<?php

namespace Draw\Bundle\UserBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class Credential
{
    #[Assert\NotBlank]
    private ?string $username = null;

    #[Assert\NotBlank]
    private ?string $password = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
