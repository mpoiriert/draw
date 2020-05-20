<?php namespace Draw\Bundle\DashboardBundle\Annotations;

class BaseAnnotation
{
    private $initialized = false;

    public function __construct(array $values = [])
    {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set' . $k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, static::class));
            }

            $this->$name($v);
        }

        $this->initialize();
        $this->initialized = true;
    }

    /**
     * Method called after the constructor execution.
     * Useful to rework some attribute when all attribute have been set.
     */
    public function initialize(): void
    {

    }

    public function assertNotInitialized(): void
    {
        if ($this->initialized) {
            throw new \RuntimeException('The annotation [' . get_class($this) . '] has been initialized.');
        }
    }
}