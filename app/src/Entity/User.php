<?php

namespace App\Entity;

use App\Message\NewUserMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDelete;
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
use Draw\Component\EntityMigrator\MigrationTargetEntityInterface;
use Draw\Component\Mailer\Recipient\LocalizationAwareInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderTrait;
use Draw\DoctrineExtra\Common\Collections\CollectionUtil;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'draw_acme__user')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'])]
class User implements MessageHolderInterface, SecurityUserInterface, TwoFactorAuthenticationUserInterface, PasswordChangeUserInterface, LockableUserInterface, TwoFactorInterface, ByEmailInterface, ByTimeBaseOneTimePasswordInterface, MigrationTargetEntityInterface, LocalizationAwareInterface
{
    use ByEmailTrait;
    use ByTimeBaseOneTimePasswordTrait;
    use ConfigurationTrait;
    use LockableUserTrait;
    use MessageHolderTrait;
    use OnBoardingLifeCycleHookUserTrait;
    use PasswordChangeEnforcerUserTrait;
    use SecurityUserTrait;

    final public const LEVEL_USER = 'user';

    final public const LEVEL_ADMIN = 'admin';

    final public const LEVELS = [
        self::LEVEL_USER,
        self::LEVEL_ADMIN,
    ];

    #[
        ORM\Id,
        ORM\Column(name: 'id', type: 'guid')
    ]
    #[Serializer\ReadOnlyProperty]
    private ?string $id = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var Collection<Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[PreventDelete(
        metadata: ['max_results' => 1]
    )]
    private Collection $tags;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => 'user'])]
    private string $level = 'user';

    #[ORM\Embedded(class: Address::class, columnPrefix: 'address_')]
    #[Assert\Valid]
    private Address $address;

    /**
     * @var Collection<UserAddress>
     */
    #[
        ORM\OneToMany(mappedBy: 'user', targetEntity: UserAddress::class, cascade: ['persist'], orphanRemoval: true),
        ORM\OrderBy(['position' => 'ASC'])
    ]
    #[Assert\Valid]
    private Collection $userAddresses;

    /**
     * User date of birth.
     */
    #[ORM\Column(name: 'date_of_birth', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[ORM\Column(type: 'text')]
    private string $comment = '';

    #[
        ORM\ManyToOne(targetEntity: ChildObject1::class),
        ORM\JoinColumn(onDelete: 'SET NULL')
    ]
    private ?ChildObject1 $childObject1 = null;

    #[
        ORM\ManyToOne(targetEntity: ChildObject2::class),
        ORM\JoinColumn(onDelete: 'SET NULL')
    ]
    private ?ChildObject2 $childObject2 = null;

    #[
        ORM\ManyToOne(targetEntity: ChildObject2::class),
        ORM\JoinColumn(onDelete: 'RESTRICT')
    ]
    private ?ChildObject2 $onDeleteRestrict = null;

    #[
        ORM\ManyToOne(targetEntity: ChildObject2::class),
        ORM\JoinColumn(onDelete: 'CASCADE')
    ]
    private ?ChildObject2 $onDeleteCascade = null;

    #[
        ORM\ManyToOne(targetEntity: ChildObject2::class),
        ORM\JoinColumn(onDelete: 'SET NULL')
    ]
    private ?ChildObject2 $onDeleteSetNull = null;

    #[
        ORM\ManyToOne(targetEntity: ChildObject2::class),
        ORM\JoinColumn(onDelete: 'CASCADE')
    ]
    private ?ChildObject2 $onDeleteCascadeConfigOverridden = null;

    #[
        ORM\ManyToOne(targetEntity: ChildObject2::class),
        ORM\JoinColumn(onDelete: 'CASCADE')
    ]
    #[PreventDelete]
    private ?ChildObject2 $onDeleteCascadeAttributeOverridden = null;

    #[
        ORM\OneToMany(mappedBy: 'user', targetEntity: UserTag::class, cascade: ['persist'], orphanRemoval: true),
    ]
    private Collection $userTags;

    #[Assert\NotNull]
    private string $requiredReadOnly = 'value';

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => 'en'])]
    private string $preferredLocale = 'en';

    public function __construct()
    {
        $this->address = new Address();
        $this->tags = new ArrayCollection();
        $this->userTags = new ArrayCollection();
        $this->userAddresses = new ArrayCollection();
        $this->setNeedChangePassword(true);
        $this->onHoldMessages[NewUserMessage::class] = new NewUserMessage($this);
    }

    #[ORM\PrePersist]
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

    public function getPreferredLocale(): string
    {
        return $this->preferredLocale;
    }

    public function setPreferredLocale(string $preferredLocale): static
    {
        $this->preferredLocale = $preferredLocale;

        return $this;
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @phpstan-param Collection<Tag>|array<int,Tag> $tags
     */
    public function setTags(Collection|array $tags): static
    {
        $this->tags = new ArrayCollection();

        foreach ($tags as $tag) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * @return Collection<UserTag>
     */
    public function getUserTags(): Collection
    {
        return $this->userTags;
    }

    public function addUserTag(UserTag $userTag): self
    {
        if (!$this->userTags->contains($userTag)) {
            $this->userTags->add($userTag);
            $userTag->setUser($this);
        }

        return $this;
    }

    public function removeUserTag(UserTag $userTag): self
    {
        if ($this->userTags->contains($userTag)) {
            $this->userTags->removeElement($userTag);
        }

        return $this;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<UserAddress>
     */
    public function getUserAddresses(): Collection
    {
        return $this->userAddresses;
    }

    public function addUserAddress(UserAddress $userAddress): static
    {
        if (!$this->userAddresses->contains($userAddress)) {
            CollectionUtil::assignPosition($userAddress, $this->userAddresses);
            $this->userAddresses->add($userAddress);
            $userAddress->setUser($this);
        }

        return $this;
    }

    public function removeUserAddress(UserAddress $userAddress): static
    {
        if ($this->userAddresses->contains($userAddress)) {
            $this->userAddresses->removeElement($userAddress);
        }

        return $this;
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

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
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

    public function getOnDeleteRestrict(): ?ChildObject2
    {
        return $this->onDeleteRestrict;
    }

    public function setOnDeleteRestrict(?ChildObject2 $onDeleteRestrict): static
    {
        $this->onDeleteRestrict = $onDeleteRestrict;

        return $this;
    }

    public function getOnDeleteCascade(): ?ChildObject2
    {
        return $this->onDeleteCascade;
    }

    public function setOnDeleteCascade(?ChildObject2 $onDeleteCascade): static
    {
        $this->onDeleteCascade = $onDeleteCascade;

        return $this;
    }

    public function getOnDeleteSetNull(): ?ChildObject2
    {
        return $this->onDeleteSetNull;
    }

    public function setOnDeleteSetNull(?ChildObject2 $onDeleteSetNull): static
    {
        $this->onDeleteSetNull = $onDeleteSetNull;

        return $this;
    }

    public function getOnDeleteCascadeConfigOverridden(): ?ChildObject2
    {
        return $this->onDeleteCascadeConfigOverridden;
    }

    public function setOnDeleteCascadeConfigOverridden(?ChildObject2 $onDeleteCascadeConfigOverridden): static
    {
        $this->onDeleteCascadeConfigOverridden = $onDeleteCascadeConfigOverridden;

        return $this;
    }

    public function getOnDeleteCascadeAttributeOverridden(): ?ChildObject2
    {
        return $this->onDeleteCascadeAttributeOverridden;
    }

    public function setOnDeleteCascadeAttributeOverridden(?ChildObject2 $onDeleteCascadeAttributeOverridden): static
    {
        $this->onDeleteCascadeAttributeOverridden = $onDeleteCascadeAttributeOverridden;

        return $this;
    }

    public function getRequiredReadOnly(): string
    {
        return $this->requiredReadOnly;
    }

    public function setRequiredReadOnly(string $requiredReadOnly): static
    {
        $this->requiredReadOnly = $requiredReadOnly;

        return $this;
    }

    public static function getEntityMigrationClass(): string
    {
        return UserMigration::class;
    }
}
