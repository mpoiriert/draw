<?php

namespace Draw\Bundle\ApplicationBundle\Configuration\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Draw\Component\Core\DateTimeUtils;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Draw\Bundle\ApplicationBundle\Configuration\Repository\ConfigRepository")
 * @ORM\Table(name="draw__config")
 * @UniqueEntity(fields={"id"})
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Config
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255, allowEmptyString=false)
     */
    private $id = null;

    /**
     * @ORM\Column(name="data", type="json", nullable=false)
     */
    private $data = ['value' => null];

    /**
     * @ORM\Column(name="updated_at", type="datetime_immutable", nullable=false)
     */
    private $updatedAt = null;

    /**
     * @ORM\Column(name="created_at", type="datetime_immutable", nullable=false)
     */
    private $createdAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getValue()
    {
        return $this->data['value'] ?: null;
    }

    public function setValue($value): self
    {
        $this->data = compact('value');

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt ?: $this->createdAt = new DateTimeImmutable();
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        if (!DateTimeUtils::isSameTimestamp($this->createdAt, $createdAt)) {
            $this->createdAt = DateTimeUtils::toDateTimeImmutable($createdAt);
        }

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt ?: $this->updatedAt = DateTimeUtils::toDateTimeImmutable($this->getCreatedAt());
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        if (!DateTimeUtils::isSameTimestamp($this->updatedAt, $updatedAt)) {
            $this->updatedAt = DateTimeUtils::toDateTimeImmutable($updatedAt);
        }

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateTimestamps()
    {
        $this->getCreatedAt();
        $this->setUpdatedAt(new DateTimeImmutable());
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
