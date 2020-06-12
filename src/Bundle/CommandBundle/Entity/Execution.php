<?php

namespace Draw\Bundle\CommandBundle\Entity;

use DateTime;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
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
    const STATE_INITIALIZED = 'initialized';

    const STATE_STARTED = 'started';

    const STATE_ERROR = 'error';

    const STATE_TERMINATED = 'terminated';

    const STATE_ACKNOWLEDGE = 'acknowledge';

    const STATES = [
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
     * @ORM\Column(name="input", type="json_array", nullable=false)
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * @param string $commandName
     */
    public function setCommandName($commandName)
    {
        $this->commandName = $commandName;
    }

    /**
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    public function setInput(array $input)
    {
        $this->input = $input;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function getOutputHtml()
    {
        $converter = new AnsiToHtmlConverter();

        return nl2br($converter->convert($this->getOutput()));
    }

    /**
     * @return string
     */
    public function getCommandLine()
    {
        return (string) (new ArrayInput($this->getInput()));
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function updateTimestamp(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('updatedAt')) {
            return;
        }

        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PrePersist()
     */
    public function ensureTimestamp(LifecycleEventArgs $eventArgs)
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
