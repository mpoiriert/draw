<?php

namespace Draw\Bundle\SonataImportBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[ORM\Entity]
#[ORM\Table(name: 'import__import')]
#[ORM\HasLifecycleCallbacks]
#[Assert\GroupSequenceProvider]
class Import implements GroupSequenceProviderInterface, \Stringable
{
    final public const STATE_NEW = 'new';

    final public const STATE_CONFIGURATION = 'configuration';

    final public const STATE_VALIDATION = 'validation';

    final public const STATE_PROCESSED = 'processed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    /**
     * The class (or class alias) of the entity you want to import.
     */
    #[ORM\Column(name: 'entity_class', type: 'string', length: 255, nullable: false)]
    private ?string $entityClass = null;

    #[ORM\Column(name: 'insert_when_not_found', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $insertWhenNotFound = false;

    /**
     * The content of the file to import.
     */
    #[ORM\Column(name: 'file_content', type: 'text', nullable: true)]
    private ?string $fileContent = null;

    /**
     * @var Selectable&Collection<Column>
     */
    #[ORM\OneToMany(mappedBy: 'import', targetEntity: Column::class, cascade: ['persist'])]
    #[Assert\Count(min: 1, groups: ['validation'])]
    #[Assert\Valid]
    private Selectable&Collection $columns;

    #[ORM\Column(name: 'state', type: 'string', length: 40, nullable: false, options: ['default' => 'new'])]
    private string $state = self::STATE_NEW;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: false)]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->columns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(?string $entityClass): static
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getInsertWhenNotFound(): bool
    {
        return $this->insertWhenNotFound;
    }

    public function setInsertWhenNotFound(bool $insertWhenNotFound): self
    {
        $this->insertWhenNotFound = $insertWhenNotFound;

        return $this;
    }

    public function getFileContent(): ?string
    {
        return $this->fileContent;
    }

    public function setFileContent(?string $fileContent): static
    {
        $this->fileContent = $fileContent;

        return $this;
    }

    /**
     * @return Selectable&Collection<Column>
     */
    public function getColumns(): Selectable&Collection
    {
        return $this->columns;
    }

    public function addColumn(Column $header): static
    {
        if (!$this->columns->contains($header)) {
            $this->columns->add($header);
            $header->setImport($this);
        }

        return $this;
    }

    public function removeColumn(Column $header): static
    {
        if ($this->columns->contains($header)) {
            $this->columns->removeElement($header);
        }

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
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

    #[
        ORM\PrePersist,
        ORM\PreUpdate
    ]
    public function updateTimestamp(LifecycleEventArgs $eventArgs): static
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }

        if ($eventArgs instanceof PreUpdateEventArgs && $eventArgs->hasChangedField('updatedAt')) {
            return $this;
        }

        $this->updatedAt = new \DateTime();

        return $this;
    }

    #[Assert\Callback(groups: ['validation'])]
    public function validateForProcessing(ExecutionContextInterface $context): void
    {
        $asIdentifier = false;
        foreach ($this->columns as $key => $column) {
            if ($column->getIsIdentifier()) {
                if ($column->getIsIgnored()) {
                    $context
                        ->buildViolation('Identifier column "{{ name }}" cannot be ignored.')
                        ->atPath('columns['.$key.']')
                        ->setParameter('{{ name }}', $column->getHeaderName())
                        ->addViolation();
                }
            }

            $asIdentifier = true;
        }

        if (!$asIdentifier) {
            $context
                ->buildViolation('You need a identifier column.')
                ->atPath('columns')
                ->addViolation();
        }
    }

    public function getGroupSequence(): array|Assert\GroupSequence
    {
        $sequences = ['Import'];
        $sequences[] = $this->getState();

        return $sequences;
    }

    public function getIdentifierHeaderName(): ?string
    {
        foreach ($this->getColumns() as $column) {
            if ($column->getIsIdentifier()) {
                return $column->getHeaderName();
            }
        }

        return null;
    }

    /**
     * @return Column[]
     */
    public function getColumnMapping(): array
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq('isIdentifier', true))
            ->andWhere(Criteria::expr()->neq('isIgnored', true));

        $mapping = [];
        foreach ($this->columns->matching($criteria) as $column) {
            $mapping[$column->getHeaderName()] = $column;
        }

        return $mapping;
    }

    /**
     * @return array<Column>
     */
    public function getIdentifierColumns(): array
    {
        $columns = $this->getColumns()
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('isIdentifier', true))
            )
            ->toArray();

        if (\count($columns) > 0) {
            return $columns;
        }

        throw new \RuntimeException('No identifier column set');
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
