<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDelete;

#[
    ORM\Entity,
    ORM\Table(name: 'acme__user_tag')
]
class UserTag
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'integer')
    ]
    private ?int $id = null;

    #[
        ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userTags'),
        ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')
    ]
    private ?User $user = null;

    #[
        ORM\ManyToOne(targetEntity: Tag::class),
        ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')
    ]
    #[PreventDelete(preventDelete: false)]
    private ?Tag $tag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function __toString(): string
    {
        return \sprintf('%s: %s', $this->getUser(), $this->getTag());
    }
}
