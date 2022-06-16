<?php

namespace Draw\Component\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class BufferedConsoleOutput extends ConsoleOutput
{
    private string $buffer = '';

    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);

        $this->setErrorOutput($this);
    }

    /**
     * This is to prevent infinite loop since the error output is self.
     */
    public function setDecorated(bool $decorated): void
    {
        if ($this->isDecorated() === $decorated) {
            return;
        }

        parent::setDecorated($decorated);
    }

    /**
     * This is to prevent infinite loop since the error output is self.
     */
    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        if ($this->getFormatter() === $formatter) {
            return;
        }

        parent::setFormatter($formatter);
    }

    /**
     * This is to prevent infinite loop since the error output is self.
     */
    public function setVerbosity(int $level)
    {
        if ($this->getVerbosity() === $level) {
            return;
        }

        parent::setVerbosity($level);
    }

    /**
     * Empties buffer and returns its content.
     */
    public function fetch(): string
    {
        $content = $this->buffer;
        $this->buffer = '';

        return $content;
    }

    protected function doWrite($message, $newline): void
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= \PHP_EOL;
        }

        parent::doWrite($message, $newline);
    }
}
