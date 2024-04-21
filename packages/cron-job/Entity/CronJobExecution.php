<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Entity;

use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'cron_job__cron_job_execution'),
]
class CronJobExecution implements \Stringable
{
    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: 'integer'),
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'requested_at', type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(name: '`force`', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $force = false;

    #[ORM\Column(name: 'execution_started_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $executionStartedAt = null;

    #[ORM\Column(name: 'execution_ended_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $executionEndedAt = null;

    #[ORM\Column(name: 'execution_delay', type: 'integer', nullable: true)]
    private ?int $executionDelay = null;

    #[ORM\Column(name: 'exit_code', type: 'integer', nullable: true)]
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
    private CronJob $cronJob;

    public function __construct(
        CronJob $cronJob,
        \DateTimeImmutable $requestedAt,
        bool $force
    ) {
        $this->cronJob = $cronJob;
        $this->requestedAt = $requestedAt;
        $this->force = $force;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    public function getExecutionStartedAt(): ?\DateTimeImmutable
    {
        return $this->executionStartedAt;
    }

    private function setExecutionStartedAt(?\DateTimeImmutable $executionStartedAt): self
    {
        $this->executionStartedAt = $executionStartedAt;

        return $this;
    }

    public function getExecutionEndedAt(): ?\DateTimeImmutable
    {
        return $this->executionEndedAt;
    }

    private function setExecutionEndedAt(?\DateTimeImmutable $executionEndedAt): self
    {
        $this->executionEndedAt = $executionEndedAt;

        return $this;
    }

    public function getExecutionDelay(): ?int
    {
        return $this->executionDelay;
    }

    private function setExecutionDelay(?int $executionDelay): self
    {
        $this->executionDelay = $executionDelay;

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    private function setExitCode(?int $exitCode): self
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function getError(): ?array
    {
        return $this->error;
    }

    private function setError(?array $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getCronJob(): ?CronJob
    {
        return $this->cronJob;
    }

    public function isExecutable(\DateTimeImmutable $dateTime): bool
    {
        if (!($cronJob = $this->getCronJob())?->isActive()) {
            return false;
        }

        if (0 === ($timeToLive = $cronJob->getTimeToLive())) {
            return true;
        }

        if (null === $this->getRequestedAt()) {
            return false;
        }

        return $dateTime->getTimestamp() <= $this->getRequestedAt()->getTimestamp() + $timeToLive;
    }

    public function start(): void
    {
        $this
            ->setExecutionStartedAt(new \DateTimeImmutable())
            ->setExecutionEndedAt(null);
    }

    public function end(): static
    {
        $this
            ->setExitCode(0)
            ->setExecutionEndedAt($executionEndedAt = new \DateTimeImmutable())
            ->setExecutionDelay(
                $executionEndedAt->getTimestamp() - $this->getExecutionStartedAt()->getTimestamp()
            );

        return $this;
    }

    public function fail(?int $exitCode, ?array $error): void
    {
        $this
            ->end()
            ->setExitCode($exitCode)
            ->setError($error);
    }

    public function __toString(): string
    {
        return implode(
            ', ',
            array_filter(
                [
                    $this->getRequestedAt()?->format('Y-m-d H:i:s.u') ?? '-',
                    $this->getExitCode(),
                    $this->getExecutionDelay(),
                ]
            )
        );
    }
}
