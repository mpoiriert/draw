<?php

namespace Draw\Bundle\UserBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Credential
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Type("string")
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
}
