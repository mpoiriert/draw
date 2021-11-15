<?php

namespace Draw\Bundle\CommandBundle\Entity;

use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
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
 *         @ORM\Index(name="state_updated", columns={"state", "updated_at"})
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

    public const STATES = [
        self::STATE_INITIALIZED,
        self::STATE_STARTED,
        self::STATE_ERROR,
        self::STATE_TERMINATED,
        self::STATE_ACKNOWLEDGE,
    ];

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     */
    private $id;

    /**
     * This is the command name when created trough the dashboard.
     *
     * @var string
     *
     * @ORM\Column(name="command", type="string", length=40, nullable=false, options={"default":"N/A"})
     */
    private $command;

    /**
     * @var string
     *
     * @ORM\Column(name="command_name", type="string", length=255, nullable=false)
     */
    private $commandName;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=40, nullable=false)
     */
    private $state;

    /**
     * @var array
     *
     * @ORM\Column(name="input", type="json", nullable=false)
     */
    private $input = [];

    /**
     * The execution output of the command.
     *
     * @var string
     *
     * @ORM\Column(name="output", type="text", nullable=false, options={"default":""})
     */
    private $output = '';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): void
    {
        $this->command = $command;
    }

    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    public function setCommandName(?string $commandName): void
    {
        $this->commandName = $commandName;
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function setInput(array $input): void
    {
        $this->input = $input;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output)
    {
        $this->output = $output;
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

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function updateTimestamp(PreUpdateEventArgs $eventArgs): void
    {
        if ($eventArgs->hasChangedField('updatedAt')) {
            return;
        }

        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PrePersist()
     */
    public function ensureTimestamp(LifecycleEventArgs $eventArgs): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new DateTime();
        }

        if (null === $this->updatedAt) {
            $this->updatedAt = $this->createdAt;
        }
    }

    public function __toString()
    {
        return (string) $this->commandName;
    }
}
