<?php

namespace Draw\Component\EntityMigrator\Entity;

use Doctrine\ORM\Mapping as ORM;
use Draw\Component\EntityMigrator\MigrationTargetEntityInterface;
use Draw\Component\EntityMigrator\Workflow\EntityMigrationWorkflow;
use Draw\Component\Log\Monolog\ErrorToArray;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseEntityMigration implements EntityMigrationInterface, \Stringable
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(type: 'bigint')
    ]
    protected ?int $id = null;

    protected MigrationTargetEntityInterface $entity;

    #[
        ORM\ManyToOne(targetEntity: Migration::class),
        ORM\JoinColumn(name: 'migration_id', nullable: false, onDelete: 'CASCADE')
    ]
    protected Migration $migration;

    #[
        ORM\Column(type: 'string', nullable: false, options: ['default' => EntityMigrationWorkflow::PLACE_NEW])
    ]
    protected string $state = EntityMigrationWorkflow::PLACE_NEW;

    #[
        ORM\Column(type: 'json', nullable: true)
    ]
    protected array $transitionLogs = [];

    #[
        ORM\Column(type: 'datetime_immutable', nullable: false)
    ]
    protected \DateTimeImmutable $createdAt;

    public function __construct(MigrationTargetEntityInterface $entity, Migration $migration)
    {
        $this->entity = $entity;
        $this->migration = $migration;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): MigrationTargetEntityInterface
    {
        return $this->entity;
    }

    public function getMigration(): Migration
    {
        return $this->migration;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state, array $context = []): static
    {
        $previousState = $this->state;

        $this->state = $state;

        $transitionName = $context['_transitionName'] ?? null;

        if ($transitionName) {
            $user = $context['_user'] ?? null;

            $createdBy = null;
            if ($user instanceof UserInterface) {
                $createdBy = $user->getUserIdentifier();
            }

            $error = $context['error'] ?? null;

            $this->transitionLogs[] = [
                'transition' => $transitionName,
                'from' => $previousState,
                'to' => $state,
                'createdAt' => time(),
                'createdBy' => $createdBy,
                'error' => $error ? ErrorToArray::convert($error) : null,
            ];
        }

        return $this;
    }

    public function getTransitionLogs(): array
    {
        return $this->transitionLogs;
    }

    public function __toString(): string
    {
        return $this->migration.' --> '.$this->entity;
    }
}
