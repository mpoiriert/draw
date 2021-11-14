<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Bundle\MessengerBundle\Entity\BaseMessengerMessage;

/**
 * @ORM\Entity()
 * @ORM\Table(name="messenger_messages")
 */
class MessengerMessage extends BaseMessengerMessage
{
}
