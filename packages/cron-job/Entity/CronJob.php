<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Entity;

use Cron\CronExpression;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity,
    ORM\Table(name: 'cron_job__cron_job'),
]
class CronJob implements \Stringable
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'integer'),
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true, nullable: false)]
    #[
        Assert\NotNull,
        Assert\Length(min: 1, max: 255),
    ]
    private ?string $name = null;

    #[ORM\Column(name: 'active', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $active = false;

    #[ORM\Column(name: 'command', type: 'text', nullable: false)]
    #[Assert\NotNull]
    private ?string $command = null;

    #[ORM\Column(name: 'schedule', type: 'string', length: 255, nullable: true)]
    private ?string $schedule = null;

    #[ORM\Column(name: 'time_to_live', type: 'integer', nullable: false, options: ['default' => 0])]
    #[
        Assert\NotNull,
        Assert\GreaterThanOrEqual(0),
    ]
    private int $timeToLive = 0;

    #[ORM\Column(name: 'execution_timeout', type: 'integer', nullable: true)]
    private ?int $executionTimeout = null;

    #[ORM\Column(name: 'priority', type: 'integer', nullable: true)]
    #[Assert\Range(min: 0, max: 255)]
    private ?int $priority = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * @var Selectable&Collection<CronJobExecution>
     */
    #[
        ORM\OneToMany(
            mappedBy: 'cronJob',
            targetEntity: CronJobExecution::class,
            cascade: ['persist'],
            fetch: 'EXTRA_LAZY',
            orphanRemoval: true,
        )
    ]
    private Selectable&Collection $executions;

    public function __construct()
    {
        $this->executions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): self
    {
        if (null !== $schedule) {
            $schedule = (new CronExpression($schedule))->getExpression();
        }

        $this->schedule = $schedule;

        return $this;
    }

    public function getTimeToLive(): int
    {
        return $this->timeToLive;
    }

    public function setTimeToLive(int $timeToLive): self
    {
        $this->timeToLive = $timeToLive;

        return $this;
    }

    public function getExecutionTimeout(): ?int
    {
        return $this->executionTimeout;
    }

    public function setExecutionTimeout(?int $executionTimeout): self
    {
        $this->executionTimeout = $executionTimeout;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Selectable&Collection<CronJobExecution>
     */
    public function getExecutions(): Collection
    {
        return $this->executions;
    }

    /**
     * @return Selectable&Collection<CronJobExecution>
     */
    public function getRecentExecutions(): Selectable&Collection
    {
        return $this->executions
            ->matching(
                Criteria::create()
                    ->orderBy(['requestedAt' => 'DESC'])
                    ->setMaxResults(10)
            );
    }

    public function isDue(): bool
    {
        if (null === $this->getSchedule()) {
            return false;
        }

        return (new CronExpression($this->getSchedule()))->isDue();
    }

    public function newExecution(bool $force = false): CronJobExecution
    {
        $cronJobExecution = new CronJobExecution($this, new \DateTimeImmutable(), $force);

        $this->executions->add($cronJobExecution);

        return $cronJobExecution;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
