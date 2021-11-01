<?php

namespace Draw\Bundle\MessengerBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="messenger_messages")
 */
class MessengerMessage
{
    /**
     * @var int|null
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="bigint")
     */
    public $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="body", type="text")
     */
    public $body;

    /**
     * @var string|null
     *
     * @ORM\Column(name="headers", type="text")
     */
    public $headers;

    /**
     * @var string|null
     *
     * @ORM\Column(name="queue_name", type="string")
     */
    public $queueName;

    /**
     * @var DateTimeImmutable|null
     *
     * @ORM\Column(name="created_at", type="datetime_immutable")
     */
    public $createdAt;

    /**
     * @var DateTimeImmutable|null
     *
     * @ORM\Column(name="available_at", type="datetime_immutable")
     */
    public $availableAt;

    /**
     * @var DateTimeImmutable|null
     *
     * @ORM\Column(name="delivered_at", type="datetime_immutable")
     */
    public $deliveredAt;

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
