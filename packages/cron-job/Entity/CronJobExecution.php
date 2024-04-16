<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Entity;

use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'cron_job__cron_job_execution'),
]
class CronJobExecution
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'integer'),
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'requested_at', type: 'datetime_immutable', nullable: false)]
    private ?\DateTimeImmutable $requestedAt = null;

    #[ORM\Column(name: 'force', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $force = false;

    #[ORM\Column(name: 'execution_started_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $executionStartedAt = null;

    #[ORM\Column(name: 'execution_ended_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $executionEndedAt = null;

    #[ORM\Column(name: 'execution_delay', type: 'int', nullable: true)]
    private ?int $executionDelay = null;

    #[ORM\Column(name: 'exit_code', type: 'int', nullable: true)]
    private ?int $exitCode = null;

    #[ORM\Column(name: 'error', type: 'json', nullable: true)]
    private ?array $error = null;

    #[
        ORM\ManyToOne(
            targetEntity: CronJob::class,
            inversedBy: 'executions',
        ),
        ORM\JoinColumn(
            name: 'cron_job_id',
            referencedColumnName: 'id',
            nullable: false,
            onDelete: 'CASCADE',
        )
    ]
    private ?CronJob $cronJob = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(?\DateTimeImmutable $requestedAt): self
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    public function setForce(bool $force): self
    {
        $this->force = $force;

        return $this;
    }

    public function getExecutionStartedAt(): ?\DateTimeImmutable
    {
        return $this->executionStartedAt;
    }

    public function setExecutionStartedAt(?\DateTimeImmutable $executionStartedAt): self
    {
        $this->executionStartedAt = $executionStartedAt;

        return $this;
    }

    public function getExecutionEndedAt(): ?\DateTimeImmutable
    {
        return $this->executionEndedAt;
    }

    public function setExecutionEndedAt(?\DateTimeImmutable $executionEndedAt): self
    {
        $this->executionEndedAt = $executionEndedAt;

        return $this;
    }

    public function getExecutionDelay(): ?int
    {
        return $this->executionDelay;
    }

    public function setExecutionDelay(?int $executionDelay): self
    {
        $this->executionDelay = $executionDelay;

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(?int $exitCode): self
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function getError(): ?array
    {
        return $this->error;
    }

    public function setError(?array $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getCronJob(): ?CronJob
    {
        return $this->cronJob;
    }

    public function setCronJob(?CronJob $cronJob): self
    {
        $this->cronJob = $cronJob;

        return $this;
    }

    public function start(): void
    {
        $this
            ->setExecutionStartedAt(new \DateTimeImmutable())
            ->setExecutionEndedAt(null);
    }

    public function end(?int $exitCode): void
    {
        $this
            ->setExitCode($exitCode)
            ->setExecutionEndedAt($executionEndedAt = new \DateTimeImmutable())
            ->setExecutionDelay($executionEndedAt->getTimestamp() - $this->getExecutionStartedAt()->getTimestamp());
    }

    public function fail(?int $exitCode, ?array $error): void
    {
        $this
            ->setExitCode($exitCode)
            ->setError($error);
    }
}
