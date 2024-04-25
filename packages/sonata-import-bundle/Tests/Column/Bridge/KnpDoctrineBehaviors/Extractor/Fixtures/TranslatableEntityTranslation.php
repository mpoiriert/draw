<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Column\Bridge\KnpDoctrineBehaviors\Extractor\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class TranslatableEntityTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column,
    ]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $label = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
