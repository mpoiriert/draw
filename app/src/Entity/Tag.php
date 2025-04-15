<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: 'App\Repository\TagRepository'),
    ORM\Table(name: 'draw_acme__tag'),
    ORM\HasLifecycleCallbacks
]
#[UniqueEntity(fields: ['name'])]
#[Serializer\ExclusionPolicy('all')]
class Tag implements \Stringable, TranslatableInterface
{
    use TranslatableTrait;
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'bigint')
    ]
    #[Serializer\Expose]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[
        Assert\NotNull,
        Assert\Length(min: 3, max: 255)
    ]
    #[Serializer\Expose]
    private ?string $name = null;

    #[ORM\Column(name: 'active', type: 'boolean', options: ['default' => 1])]
    #[Serializer\Expose]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->translate(fallbackToDefault: false)->getLabel();
    }

    public function setLabel(?string $label): static
    {
        $this->translate(fallbackToDefault: false)->setLabel($label);

        return $this;
    }

    #[
        Serializer\VirtualProperty,
        Serializer\SerializedName('virtualProperty')
    ]
    public function getVirtualProperty(): string
    {
        return 'Virtual property';
    }

    /**
     * @return array<int>
     */
    #[
        Serializer\VirtualProperty,
        Serializer\SerializedName('virtualPropertyArray')
    ]
    public function getVirtualPropertyArray(): array
    {
        return [1];
    }

    #[ORM\PreFlush]
    public function preFlush(): void
    {
        $this->mergeNewTranslations();
    }

    public function __toString(): string
    {
        return (string) $this->getLabel();
    }
}
