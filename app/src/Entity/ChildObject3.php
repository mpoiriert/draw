<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ChildObject3 extends BaseObject
{
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateTimeImmutable = null;

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
