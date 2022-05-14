<?php

namespace Draw\Component\Console\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Draw\Component\Core\DateTimeUtils;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="command__execution",
 *     indexes={
 *         @ORM\Index(name="state", columns={"state"}),
 *         @ORM\Index(name="command", columns={"command"}),
 *         @ORM\Index(name="command_name", columns={"command_name"}),
 *         @ORM\Index(name="state_updated", columns={"state", "updated_at"}),
 *         @ORM\Index(name="auto_acknowledge_reason", columns={"auto_acknowledge_reason"})
 *     }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Execution
{
    public const STATE_INITIALIZED = 'initialized';

    public const STATE_STARTED = 'started';

    public const STATE_ERROR = 'error';

    public const STATE_TERMINATED = 'terminated';

    public const STATE_ACKNOWLEDGE = 'acknowledge';

    public const STATE_AUTO_ACKNOWLEDGE = 'auto_acknowledge';

    public const STATES = [
        self::STATE_INITIALIZED,
        self::STATE_STARTED,
        self::STATE_ERROR,
        self::STATE_TERMINATED,
        self::STATE_ACKNOWLEDGE,
        self::STATE_AUTO_ACKNOWLEDGE,
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     */
    private ?int $id = null;

    /**
     * This is the command name when created trough the dashboard.
     *
     * @ORM\Column(name="command", type="string", length=40, nullable=false, options={"default":"N/A"})
     */
    private ?string $command = null;

    /**
     * @ORM\Column(name="command_name", type="string", length=255, nullable=false)
     */
    private ?string $commandName = null;

    /**
     * @ORM\Column(name="state", type="string", length=40, nullable=false)
     */
    private ?string $state = null;

    /**
     * @ORM\Column(name="input", type="json", nullable=false)
     */
    private array $input = [];

    /**
     * The execution output of the command.
     *
     * @ORM\Column(name="output", type="text", nullable=false, options={"default":""})
     */
    private string $output = '';

    /**
     * @ORM\Column(name="created_at", type="datetime_immutable", nullable=false)
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime_immutable", nullable=false)
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @ORM\Column(name="auto_acknowledge_reason", type="string", nullable=true)
     */
    private ?string $autoAcknowledgeReason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

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

    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    public function setCommandName(?string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function setInput(array $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function setOutput(string $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt ?: $this->createdAt = new DateTimeImmutable();
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        if (!DateTimeUtils::isSameTimestamp($this->createdAt, $createdAt)) {
            $this->createdAt = DateTimeUtils::toDateTimeImmutable($createdAt);
        }

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt ?: $this->updatedAt = $this->getCreatedAt();
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        if (!DateTimeUtils::isSameTimestamp($this->updatedAt, $updatedAt)) {
            $this->updatedAt = DateTimeUtils::toDateTimeImmutable($updatedAt);
        }

        return $this;
    }

    public function getAutoAcknowledgeReason(): ?string
    {
        return $this->autoAcknowledgeReason;
    }

    public function setAutoAcknowledgeReason(?string $autoAcknowledgeReason): self
    {
        $this->autoAcknowledgeReason = $autoAcknowledgeReason;

        return $this;
    }

    public function getOutputHtml(): string
    {
        $converter = new AnsiToHtmlConverter();

        return nl2br($converter->convert($this->getOutput()));
    }

    public function getCommandLine(): string
    {
        return (string) (new ArrayInput($this->getInput()));
    }

    /**
     * @ORM\PreUpdate()
     */
    public function updateTimestamp(PreUpdateEventArgs $eventArgs): void
    {
        if (!$eventArgs->hasChangedField('updatedAt')) {
            if (null === $this->updatedAt) {
                $this->getUpdatedAt();

                return;
            }
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function __toString(): string
    {
        return (string) $this->commandName;
    }
}
