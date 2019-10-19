<?php namespace Draw\Bundle\CronBundle\Model;

class Job
{
    /**
     * The name of the job for reference
     *
     * @var string
     */
    private $name;

    /**
     * The description of the job
     *
     * @var string
     */
    private $description;

    /**
     * The cron execution expression configuration
     *
     * @var string
     */
    private $expression = '* * * * *';

    /**
     * The command to execute
     *
     * @var string
     */
    private $command;

    /**
     * If the job is enabled or not
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * Where the output will be redirect
     *
     * @var string
     */
    private $output = '>/dev/null 2>&1';

    public function __construct($name, $command, $expression = '* * * * *', $enabled = true, $description = '')
    {
        $this->name = $name;
        $this->expression = $expression;
        $this->command = $command;
        $this->enabled = $enabled;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function setExpression(string $expression)
    {
        $this->expression = $expression;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command)
    {
        $this->command = $command;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}