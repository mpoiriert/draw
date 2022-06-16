<?php

namespace Draw\Component\Messenger\Transport\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

trait DrawMessageTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="guid")
     */
    private ?string $id = null;

    /**
     * @ORM\Column(name="body", type="text")
     */
    private ?string $body = null;

    /**
     * @ORM\Column(name="headers", type="text")
     */
    private ?string $headers = null;

    /**
     * @ORM\Column(name="queue_name", type="string")
     */
    private ?string $queueName = null;

    /**
     * @ORM\Column(name="created_at", type="datetime_immutable")
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(name="available_at", type="datetime_immutable")
     */
    private ?\DateTimeImmutable $availableAt = null;

    /**
     * @ORM\Column(name="delivered_at", type="datetime_immutable")
     */
    private ?\DateTimeImmutable $deliveredAt = null;

    /**
     * @ORM\Column(name="expires_at", type="datetime_immutable")
     */
    private ?\DateTimeImmutable $expiresAt = null;

    /**
     * @var Collection|DrawMessageTagInterface[]|null
     *
     * @ORM\OneToMany(
     *     targetEntity="Draw\Component\Messenger\Transport\Entity\DrawMessageTagInterface",
     *     mappedBy="message"
     * )
     */
    private ?Collection $tags = null;

    public function getId(): string
    {
        if (null === $this->id) {
            $this->id = Uuid::uuid6()->toString();
        }

        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->getId();
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    public function setHeaders(?string $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getQueueName(): ?string
    {
        return $this->queueName;
    }

    public function setQueueName(?string $queueName): self
    {
        $this->queueName = $queueName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAvailableAt(): ?\DateTimeImmutable
    {
        return $this->availableAt;
    }

    public function setAvailableAt(?\DateTimeImmutable $availableAt): self
    {
        $this->availableAt = $availableAt;

        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeImmutable $deliveredAt): self
    {
        $this->deliveredAt = $deliveredAt;

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags ?: $this->tags = new ArrayCollection();
    }

    public function addTag(DrawMessageTagInterface $tag): self
    {
        if (!$this->getTags()->contains($tag)) {
            $this->getTags()->add($tag);
        }

        return $this;
    }

    public function removeTag(DrawMessageTagInterface $tag): self
    {
        if ($this->getTags()->contains($tag)) {
            $this->getTags()->removeElement($tag);
        }

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getMessageId();
    }
}
