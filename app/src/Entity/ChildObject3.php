<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ChildObject3 extends BaseObject
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
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
