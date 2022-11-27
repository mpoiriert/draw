<?php

namespace App\Entity;

use App\Message\NewUserMessage;
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
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByEmailInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByEmailTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByTimeBaseOneTimePasswordInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByTimeBaseOneTimePasswordTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ConfigurationTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\TwoFactorAuthenticationUserInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Entity\MessageHolderTrait;
use Draw\DoctrineExtra\Common\Collections\CollectionUtil;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="draw_acme__user")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields={"email"})
 */
class User implements MessageHolderInterface, SecurityUserInterface, TwoFactorAuthenticationUserInterface, PasswordChangeUserInterface, LockableUserInterface, TwoFactorInterface, ByEmailInterface, ByTimeBaseOneTimePasswordInterface
{
    use ByEmailTrait;
    use ByTimeBaseOneTimePasswordTrait;
    use ConfigurationTrait;
    use LockableUserTrait;
    use MessageHolderTrait;
    use OnBoardingLifeCycleHookUserTrait;
    use PasswordChangeEnforcerUserTrait;
    use SecurityUserTrait;

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

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\ChildObject1"
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?ChildObject1 $childObject1;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\ChildObject2"
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?ChildObject2 $childObject2;

    public function __construct()
    {
        $this->address = new Address();
        $this->tags = new ArrayCollection();
        $this->userAddresses = new ArrayCollection();
        $this->setNeedChangePassword(true);
        $this->onHoldMessages[NewUserMessage::class] = new NewUserMessage($this);
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

    public function setId(string $id): void
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

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return Collection<Tag>
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
     * @return Collection<UserAddress>
     */
    public function getUserAddresses()
    {
        return $this->userAddresses;
    }

    public function addUserAddress(UserAddress $userAddress): void
    {
        if (!$this->userAddresses->contains($userAddress)) {
            CollectionUtil::assignPosition($userAddress, $this->userAddresses);
            $this->userAddresses->add($userAddress);
            $userAddress->setUser($this);
        }
    }

    public function removeUserAddress(UserAddress $userAddress): void
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

    public function getChildObject1(): ?ChildObject1
    {
        return $this->childObject1;
    }

    public function setChildObject1(?ChildObject1 $childObject1): void
    {
        $this->childObject1 = $childObject1;
    }

    public function getChildObject2(): ?ChildObject2
    {
        return $this->childObject2;
    }

    public function setChildObject2(?ChildObject2 $childObject2): void
    {
        $this->childObject2 = $childObject2;
    }
}
