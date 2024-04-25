<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\KnpDoctrineBehaviors\Extractor\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[ORM\Entity]
class TranslatableEntity implements TranslatableInterface
{
    use TranslatableTrait;

    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column
    ]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
