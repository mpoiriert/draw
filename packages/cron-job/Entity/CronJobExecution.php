<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'cron_job__cron_job_execution'),
    ORM\Index(name: 'state', fields: ['state']),
]
class CronJobExecution implements \Stringable
{
    public const string STATE_REQUESTED = 'requested';
    public const string STATE_RUNNING = 'running';
    public const string STATE_TERMINATED = 'terminated';
    public const string STATE_ERRORED = 'errored';
    public const string STATE_SKIPPED = 'skipped';
    public const string STATE_ACKNOWLEDGED = 'acknowledged';

    public const array STATES = [
        self::STATE_REQUESTED,
        self::STATE_RUNNING,
        self::STATE_TERMINATED,
        self::STATE_ERRORED,
        self::STATE_SKIPPED,
        self::STATE_ACKNOWLEDGED,
    ];

    #[
        ORM\Id,
        ORM\GeneratedValue,
        ORM\Column(name: 'id', type: Types::INTEGER),
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'requested_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(name: 'state', type: Types::STRING, length: 20, nullable: false, options: ['default' => self::STATE_REQUESTED])]
    private string $state = self::STATE_REQUESTED;

    #[ORM\Column(name: '`force`', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $force;

    #[ORM\Column(name: 'execution_started_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $executionStartedAt = null;

    #[ORM\Column(name: 'execution_ended_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $executionEndedAt = null;

    #[ORM\Column(name: 'execution_delay', type: Types::INTEGER, nullable: true)]
    private ?int $executionDelay = null;

    #[ORM\Column(name: 'exit_code', type: Types::INTEGER, nullable: true)]
    private ?int $exitCode = null;

    #[ORM\Column(name: 'error', type: Types::TEXT, nullable: true)]
    private ?string $error = null;

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
        bool $force,
    ) {
        $this->cronJob = $cronJob;
        $this->requestedAt = $requestedAt;
        $this->force = $force;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getState(): string
    {
        return $this->state;
    }

    private function setState(string $state): self
    {
        $this->state = $state;

        return $this;
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

    public function getError(): ?string
    {
        return $this->error;
    }

    private function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getCronJob(): CronJob
    {
        return $this->cronJob;
    }

    public function isExecutable(\DateTimeImmutable $dateTime): bool
    {
        $cronJob = $this->getCronJob();

        if (!$this->isForce() && !$cronJob->isActive()) {
            return false;
        }

        if (0 === ($timeToLive = $cronJob->getTimeToLive())) {
            return true;
        }

        return $dateTime->getTimestamp() <= $this->getRequestedAt()->getTimestamp() + $timeToLive;
    }

    public function start(): void
    {
        $this
            ->setState(self::STATE_RUNNING)
            ->setExecutionStartedAt(new \DateTimeImmutable())
            ->setExecutionEndedAt(null)
        ;
    }

    public function end(): static
    {
        $this
            ->setState(self::STATE_TERMINATED)
            ->setExitCode(0)
            ->setExecutionEndedAt($executionEndedAt = new \DateTimeImmutable())
            ->setExecutionDelay(
                $executionEndedAt->getTimestamp() - $this->getExecutionStartedAt()->getTimestamp()
            )
        ;

        return $this;
    }

    public function fail(?int $exitCode, string $error): void
    {
        $this
            ->end()
            ->setState(self::STATE_ERRORED)
            ->setExitCode($exitCode)
            ->setError($error)
        ;
    }

    public function acknowledge(): void
    {
        $this->setState(self::STATE_ACKNOWLEDGED);
    }

    public function skip(): void
    {
        $this->setState(self::STATE_SKIPPED);
    }

    public function canBeAcknowledged(): bool
    {
        return self::STATE_ERRORED === $this->getState();
    }

    public function __toString(): string
    {
        return implode(
            ', ',
            array_filter(
                [
                    $this->getRequestedAt()->format('Y-m-d H:i:s.u') ?? '-',
                    $this->getExitCode(),
                    $this->getExecutionDelay(),
                ]
            )
        );
    }
}
