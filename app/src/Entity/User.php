<?php

namespace App\Entity;

use App\Message\UserCreatedMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DashboardBundle\Annotations as Dashboard;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderTrait;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Draw\Bundle\UserBundle\Entity\TwoFactorAuthenticationUserTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthenticationUserInterface;
use Draw\Component\OpenApi\Doctrine\CollectionUtil;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_acme__user")
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity(fields={"email"})
 *
 * @Dashboard\FormLayout\GridList(
 *     cols=1,
 *     tiles={
 *       @Dashboard\FormLayout\GridListTile(inputs={"*"})
 *     }
 * )
 */
class User implements MessageHolderInterface, SecurityUserInterface, TwoFactorAuthenticationUserInterface
{
    use MessageHolderTrait;
    use SecurityUserTrait;
    use TwoFactorAuthenticationUserTrait;

    public const LEVEL_USER = 'user';

    public const LEVEL_ADMIN = 'admin';

    public const LEVELS = [
        User::LEVEL_USER,
        User::LEVEL_ADMIN,
    ];

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     *
     * @Dashboard\Column(
     *      sortable=true
     * )
     *
     * @Serializer\ReadOnly()
     *
     * @Dashboard\Filter(
     *     input=@Dashboard\FormInputAutoComplete(
     *          parameters=@Dashboard\EntityParameters(class=User::class, fields={"email"})
     *     )
     * )
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var Tag[]|Collection
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\Tag"
     * )
     *
     * @Dashboard\Column(
     *     type="list",
     *     sortable=false,
     *     options={"list": {"attribute":"label"}}
     * )
     *
     * @Dashboard\FormInputChoices(
     *     multiple=true,
     *     expression="repository.findActive()"
     * )
     *
     * @Dashboard\Filter(
     *     input=@Dashboard\FormInputChoices()
     * )
     */
    private $tags;

    /**
     * @var string
     *
     * @ORM\Column(name="level", type="string", nullable=false, options={"default":"user"})
     *
     * @Dashboard\Column(
     *     type="choices",
     *     options={"choices"=@Dashboard\Choices(User::LEVELS, assoc=true)}
     * )
     *
     * @Dashboard\FormInputChoices(choices=User::LEVELS)
     *
     * @Dashboard\Filter(
     *     input=@Dashboard\FormInputChoices(choices=User::LEVELS, multiple=true),
     *     comparison="IN"
     * )
     */
    private $level = 'user';

    /**
     * @var Address
     *
     * @ORM\Embedded(class="App\Entity\Address", columnPrefix="address_")
     *
     * @Assert\Valid()
     *
     * @Dashboard\FormInputComposite()
     */
    private $address;

    /**
     * @var UserAddress[]|Collection
     *
     * @Assert\Valid()
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\UserAddress",
     *     cascade={"persist"},
     *     mappedBy="user",
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position":"ASC"})
     *
     * @Dashboard\FormInputCollection(
     *     orderBy="position",
     *     allowRemove=true
     * )
     */
    private $userAddresses;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(name="date_of_birth", type="datetime_immutable", nullable=true)
     *
     * @Dashboard\FormInputDatePicker()
     */
    private $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Dashboard\FormInput(
     *     excludeIf="!is_granted('ROLE_ADMIN')"
     * )
     */
    private $comment = '';

    public function __construct()
    {
        $this->address = new Address();
        $this->tags = new ArrayCollection();
        $this->userAddresses = new ArrayCollection();
    }

    /**
     * @ORM\PostPersist()
     */
    public function raiseUserCreated()
    {
        $this->messageQueue()->push(new UserCreatedMessage($this));
    }

    /**
     * @return string
     *
     * @ORM\PrePersist()
     */
    public function getId()
    {
        if (null === $this->id) {
            $this->id = Uuid::uuid4()->toString();
        }

        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // guarantee every user at least has ROLE_USER

        return $roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return Tag[]|Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[]|Collection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function setUserAddresses($userAddresses)
    {
        CollectionUtil::replace(
            $this,
            'userAddresses',
            $userAddresses
        );
    }

    /**
     * @return UserAddress[]|Collection
     */
    public function getUserAddresses()
    {
        return $this->userAddresses;
    }

    public function addUserAddress(UserAddress $userAddress)
    {
        if (!$this->userAddresses->contains($userAddress)) {
            CollectionUtil::assignPosition($userAddress, $this->userAddresses);
            $this->userAddresses->add($userAddress);
            $userAddress->setUser($this);
        }
    }

    public function removeUserAddress(UserAddress $userAddress)
    {
        if ($this->userAddresses->contains($userAddress)) {
            $this->userAddresses->removeElement($userAddress);
        }
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeImmutable $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }
}
