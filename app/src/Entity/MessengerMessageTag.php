<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTagInterface;
use Draw\Component\Messenger\Transport\Entity\DrawMessageTagTrait;

#[
    ORM\Entity,
    ORM\Table(name: 'draw_messenger__message_tag')
]
class MessengerMessageTag implements DrawMessageTagInterface
{
    use DrawMessageTagTrait;
}
