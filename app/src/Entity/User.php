<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\LockableUserTrait;
use Draw\Bundle\UserBundle\Entity\OnBoardingLifeCycleHookUserTrait;
use Draw\Bundle\UserBundle\Entity\PasswordChangeEnforcerUserTrait;
use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Draw\Bundle\UserBundle\Entity\TwoFactorAuthenticationUserTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait;
use Draw\DoctrineExtra\Common\Collections\CollectionUtil;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="draw_acme__user")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields={"email"})
 */
class User implements MessageHolderInterface, SecurityUserInterface, TwoFactorAuthenticationUserInterface, PasswordChangeUserInterface, LockableUserInterface
{
    use LockableUserTrait;
    use MessageHolderTrait;
    use OnBoardingLifeCycleHookUserTrait;
    use PasswordChangeEnforcerUserTrait;
    use SecurityUserTrait;
    use TwoFactorAuthenticationUserTrait;

    public const LEVEL_USER = 'user';

    public const LEVEL_ADMIN = 'admin';

    public const LEVELS = [
        self::LEVEL_USER,
        self::LEVEL_ADMIN,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     *
     * @Serializer\ReadOnlyProperty
     */
    private ?string $id = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var Collection<Tag>
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\Tag"
     * )
     */
    private Collection $tags;

    /**
     * @ORM\Column(name="level", type="string", nullable=false, options={"default": "user"})
     */
    private string $level = 'user';

    /**
     * @ORM\Embedded(class="App\Entity\Address", columnPrefix="address_")
     *
     * @Assert\Valid
     */
    private Address $address;

    /**
     * @var UserAddress[]|Collection
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\UserAddress",
     *     cascade={"persist"},
     *     mappedBy="user",
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position": "ASC"})
     */
    private Collection $userAddresses;

    /**
     * User date of birth.
     *
     * @ORM\Column(name="date_of_birth", type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $dateOfBirth = null;

    /**
     * @ORM\Column(type="text")
     */
    private string $comment = '';

    public function __construct()
    {
        $this->address = new Address();
        $this->tags = new ArrayCollection();
        $this->userAddresses = new ArrayCollection();
        $this->setNeedChangePassword(true);
    }

    /**
     * @ORM\PrePersist
     */
    public function getId(): string
    {
        if (null === $this->id) {
            $this->id = Uuid::uuid6()->toString();
        }

        return $this->id;
    }

    public function setId(string $id)
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
     * @phpstan-param Collection<Tag>|array<int,Tag> $tags
     *
     * @param mixed $tags
     */
    public function setTags($tags): void
    {
        $this->tags = new ArrayCollection();

        foreach ($tags as $tag) {
            $this->tags->add($tag);
        }
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
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

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getRolesList(): array
    {
        return $this->getRoles();
    }
}
