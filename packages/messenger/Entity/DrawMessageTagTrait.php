<?php

namespace Draw\Component\Messenger\Entity;

use Doctrine\ORM\Mapping as ORM;

trait DrawMessageTagTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(
     *     targetEntity="Draw\Component\Messenger\Entity\DrawMessageInterface",
     *     inversedBy="tags",
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?DrawMessageInterface $message = null;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="name", type="string")
     */
    private ?string $name = null;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMessage(): ?DrawMessageInterface
    {
        return $this->message;
    }

    public function setMessage(?DrawMessageInterface $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}