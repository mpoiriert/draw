<?php

namespace Draw\Bundle\MessengerBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait DrawMessageTrait
{
    /**
     * @var string|null
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="guid")
     */
    public $id;

    /**
     * @var DateTimeImmutable|null
     *
     * @ORM\Column(name="expires_at", type="datetime_immutable")
     */
    public $expiresAt;

    /**
     * @var Collection|DrawMessageTagInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Draw\Bundle\MessengerBundle\Entity\DrawMessageTagInterface",
     *     mappedBy="message"
     * )
     */
    private $tags;

    public function getMessageId(): ?string
    {
        return ((string) $this->id) ?: null;
    }

    public function getTags(): Collection
    {
        return $this->tags ?: $this->tags = new ArrayCollection();
    }
}
