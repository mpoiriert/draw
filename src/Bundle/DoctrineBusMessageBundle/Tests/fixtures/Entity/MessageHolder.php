<?php

namespace Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderTrait;

/**
 * @ORM\Entity()
 */
class MessageHolder implements MessageHolderInterface
{
    use MessageHolderTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    public $id = null;
}
