<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Component\Messenger\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Entity\DrawMessageTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_messenger__message")
 */
class MessengerMessage implements DrawMessageInterface
{
    use DrawMessageTrait;
}
