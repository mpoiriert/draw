<?php

namespace Draw\Bundle\MessengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait DrawMessageTagTrait
{
    /**
     * @var DrawMessageInterface|null
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(
     *     targetEntity="Draw\Bundle\MessengerBundle\Entity\DrawMessageInterface",
     *     inversedBy="tags",
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $message;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getMessage(): ?DrawMessageInterface
    {
        return $this->message;
    }

    public function setMessage(?DrawMessageInterface $message): void
    {
        $this->message = $message;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
