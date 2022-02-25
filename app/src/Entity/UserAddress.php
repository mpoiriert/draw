<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="acme__user_address")
 */
class UserAddress
{
    /**
     * @var int|null
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="userAddresses"
     * )
     *
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var Address
     *
     * @ORM\Embedded(class="App\Entity\Address")
     *
     * @Serializer\Type("App\Entity\Address")
     *
     * @Assert\Valid()
     */
    private $address;

    /**
     * @var int|null
     *
     * @ORM\Column(name="position", type="integer", options={"default":"0"}, nullable=false)
     */
    private $position;

    public function __construct()
    {
        $this->address = new Address();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        if ($this->user && ($this->user !== $user)) {
            throw new \RuntimeException('Cannot change user');
        }

        $this->user = $user;
        $user->addUserAddress($this);
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
