<?php

namespace Draw\Bundle\SonataImportBundle\Entity;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'import__column')]
#[ORM\UniqueConstraint(name: 'import_header_name', columns: ['import_id', 'header_name'])]
#[ORM\HasLifecycleCallbacks]
class Column
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Import::class, inversedBy: 'columns')]
    #[ORM\JoinColumn(name: 'import_id', nullable: false, onDelete: 'CASCADE')]
    private ?Import $import = null;

    /**
     * Name of the header.
     */
    #[ORM\Column(name: 'header_name', type: 'string', length: 255, nullable: false)]
    private ?string $headerName = null;

    /**
     * Sample.
     */
    #[ORM\Column(name: 'sample', type: 'text', nullable: false)]
    private ?string $sample = null;

    #[ORM\Column(name: 'is_identifier', type: 'boolean', options: ['default' => 0])]
    private ?bool $isIdentifier = null;

    #[ORM\Column(name: 'is_ignored', type: 'boolean', options: ['default' => 0])]
    private ?bool $isIgnored = null;

    /**
     * To which attribute this column mapped to. If not mapped it must be ignored.
     */
    #[ORM\Column(name: 'mapped_to', type: 'string', nullable: true)]
    private ?string $mappedTo = null;

    #[ORM\Column(name: 'is_date', type: 'boolean', nullable: false, options: ['default' => 0])]
    private ?bool $isDate = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: false)]
    private ?\DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getImport(): ?Import
    {
        return $this->import;
    }

    public function setImport(?Import $import): static
    {
        $this->import = $import;

        return $this;
    }

    public function getHeaderName(): ?string
    {
        return $this->headerName;
    }

    public function setHeaderName(?string $headerName): static
    {
        $this->headerName = $headerName;

        return $this;
    }

    public function getSample(): ?string
    {
        return $this->sample;
    }

    public function setSample(?string $sample): static
    {
        $this->sample = $sample;

        return $this;
    }

    public function getIsIdentifier(): ?bool
    {
        return $this->isIdentifier;
    }

    public function setIsIdentifier(?bool $isIdentifier): static
    {
        $this->isIdentifier = $isIdentifier;

        return $this;
    }

    public function getIsIgnored(): ?bool
    {
        return $this->isIgnored;
    }

    public function setIsIgnored(?bool $isIgnored): static
    {
        $this->isIgnored = $isIgnored;

        return $this;
    }

    public function getMappedTo(): ?string
    {
        return $this->mappedTo;
    }

    public function setMappedTo(?string $mappedTo): static
    {
        $this->mappedTo = $mappedTo;

        return $this;
    }

    public function getIsDate(): ?bool
    {
        return $this->isDate;
    }

    public function setIsDate(?bool $isDate): static
    {
        $this->isDate = $isDate;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamp(PreUpdateEventArgs|PrePersistEventArgs $eventArgs): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        if ($eventArgs instanceof PreUpdateEventArgs && $eventArgs->hasChangedField('updatedAt')) {
            return;
        }

        $this->updatedAt = new \DateTime();
    }
}
