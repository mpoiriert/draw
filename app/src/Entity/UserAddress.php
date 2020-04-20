<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use JMS\Serializer\Annotation as Serializer;

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
     *
     * @Dashboard\FormInput(
     *     type="hidden"
     * )
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
     * @Dashboard\FormInput(
     *     type="composite"
     * )
     */
    private $address;

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
        if($this->user && ($this->user !== $user)) {
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

    public function __toString()
    {
        return $this->getAddress()->getStreet();
    }
}