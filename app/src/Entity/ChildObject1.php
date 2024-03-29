<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ChildObject1 extends BaseObject
{
    #[ORM\Column(name: 'attribute_1', type: 'string')]
    private ?string $attribute1 = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateTimeImmutable = null;

    public function getAttribute1(): ?string
    {
        return $this->attribute1;
    }

    public function setAttribute1(?string $attribute1): static
    {
        $this->attribute1 = $attribute1;

        return $this;
    }

    public function getDateTimeImmutable(): ?\DateTimeImmutable
    {
        return $this->dateTimeImmutable;
    }

    public function setDateTimeImmutable(?\DateTimeImmutable $dateTimeImmutable): static
    {
        $this->dateTimeImmutable = $dateTimeImmutable;

        return $this;
    }
}
