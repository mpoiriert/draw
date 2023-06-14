<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ChildObject2 extends BaseObject
{
    #[ORM\Column(name: 'attribute_2', type: 'string')]
    private ?string $attribute2 = null;

    public function getAttribute2(): ?string
    {
        return $this->attribute2;
    }

    public function setAttribute2(?string $attribute2): static
    {
        $this->attribute2 = $attribute2;

        return $this;
    }
}
