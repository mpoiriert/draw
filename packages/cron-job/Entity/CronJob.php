<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Entity;

use Cron\CronExpression;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    private ?string $name = null;

    #[ORM\Column(name: 'active', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $active = false;

    #[ORM\Column(name: 'command', type: 'text', nullable: false)]
    private ?string $command = null;

    #[ORM\Column(name: 'schedule', type: 'string', length: 255, nullable: true)]
    private ?string $schedule = null;

    #[ORM\Column(name: 'time_to_live', type: 'integer', nullable: false, options: ['default' => 0])]
    private int $timeToLive = 0;

    #[ORM\Column(name: 'priority', type: 'integer', nullable: true)]
    private ?int $priority = null;

    /**
     * @var Collection<CronJobExecution>
     */
    #[
        ORM\OneToMany(
            mappedBy: 'cronJob',
            targetEntity: CronJobExecution::class,
            cascade: ['persist'],
            orphanRemoval: true,
        )
    ]
    private Collection $executions;

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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return Collection<CronJobExecution>
     */
    public function getExecutions(): Collection
    {
        return $this->executions;
    }

    public function addExecution(CronJobExecution $execution): self
    {
        if (!$this->executions->contains($execution)) {
            $this->executions->add($execution);
            $execution->setCronJob($this);
        }

        return $this;
    }

    public function removeExecution(CronJobExecution $execution): self
    {
        if ($this->executions->contains($execution)) {
            $this->executions->removeElement($execution);
            $execution->setCronJob(null);
        }

        return $this;
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
        return (new CronJobExecution())
            ->setCronJob($this)
            ->setRequestedAt(new \DateTimeImmutable())
            ->setForce($force);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
