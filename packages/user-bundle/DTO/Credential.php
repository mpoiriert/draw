<?php

namespace Draw\Bundle\UserBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Credential
{
    /**
     * @Assert\NotBlank()
     *
     * @Serializer\Type("string")
     */
    private ?string $username = null;

    /**
     * @Assert\NotBlank()
     *
     * @Serializer\Type("string")
     */
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
