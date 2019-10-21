<?php namespace Draw\Bundle\CommandBundle;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class BufferedConsoleOutput extends ConsoleOutput
{
    private $buffer = '';

    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);

        $this->setErrorOutput($this);
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        if($this->isDecorated() == $decorated) {
            return;
        }

        parent::setDecorated($decorated);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        if($this->getFormatter() === $formatter) {
            return;
        }

        parent::setFormatter($formatter);
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        if($this->getVerbosity() == $level) {
            return;
        }

        parent::setVerbosity($level);
    }

    /**
     * Empties buffer and returns its content.
     *
     * @return string
     */
    public function fetch()
    {
        $content = $this->buffer;
        $this->buffer = '';

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= PHP_EOL;
        }

        parent::doWrite($message, $newline);
    }
}