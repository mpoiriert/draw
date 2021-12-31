<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageInterface;
use Draw\Bundle\MessengerBundle\Entity\DrawMessageTrait;
use Draw\Bundle\MessengerBundle\Entity\MessengerMessageTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_messenger__message")
 */
class MessengerMessage implements DrawMessageInterface
{
    use MessengerMessageTrait;
    use DrawMessageTrait;
}
