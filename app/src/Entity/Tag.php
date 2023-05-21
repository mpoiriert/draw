<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: 'App\Repository\TagRepository'),
    ORM\Table(name: 'draw_acme__tag'),
    UniqueEntity(fields: ['label'])
]
class Tag implements \Stringable
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'bigint')
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'active', type: 'boolean', options: ['default' => 1])]
    private bool $active = true;

    #[ORM\Column(name: 'label', type: 'string', length: 255, nullable: false)]
    #[Assert\NotNull]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $label = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('virtualProperty')]
    public function getVirtualProperty(): string
    {
        return 'Virtual property';
    }

    /**
     * @return array<int>
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('virtualPropertyArray')]
    public function getVirtualPropertyArray(): array
    {
        return [1];
    }

    public function __toString(): string
    {
        return (string) $this->getLabel();
    }
}
