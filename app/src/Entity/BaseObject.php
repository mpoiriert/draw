<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'draw_acme__base_object'),
    ORM\InheritanceType('SINGLE_TABLE'),
    ORM\DiscriminatorColumn(name: 'discriminator_type'),
    ORM\DiscriminatorMap(
        value: [
            'child-1' => ChildObject1::class,
            'child-2' => ChildObject2::class,
            'child-3' => ChildObject3::class,
        ]
    )
]
abstract class BaseObject implements \Stringable
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'integer')
    ]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
