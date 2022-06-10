<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Component\Messenger\Transport\Entity\DrawMessageInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="draw_messenger__message")
 */
class MessengerMessage implements DrawMessageInterface
{
    use DrawMessageTrait;
}
