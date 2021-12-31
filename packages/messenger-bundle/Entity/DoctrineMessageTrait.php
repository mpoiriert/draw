<?php

namespace Draw\Bundle\MessengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait DoctrineMessageTrait
{
    /**
     * @var int|null
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="bigint")
     */
    public $id;

    public function getMessageId(): ?string
    {
        return ((string) $this->id) ?: null;
    }
}
