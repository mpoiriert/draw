<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageTagInterface;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageTagTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_messenger__message_tag")
 */
class MessengerMessageTag implements DrawMessageTagInterface
{
    use DrawMessageTagTrait;
}
