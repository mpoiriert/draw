<?php

namespace Draw\Bundle\UserBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Entity\TwoFactorAuthenticationUserTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;

/**
 * @ORM\Entity()
 */
class User implements TwoFactorAuthenticationUserInterface
{
    use TwoFactorAuthenticationUserTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="username", type="string")
     */
    private ?string $username = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }
}
