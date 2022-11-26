<?php

namespace Draw\Component\Core;

class DynamicArrayObject extends \ArrayObject
{
    final public function __construct($input, $flags = 0, $iterator_class = 'ArrayIterator')
    {
        parent::__construct($input, $flags, $iterator_class);

        if (null === $input) {
            return;
        }

        foreach ($input as $key => $value) {
            if (\is_array($value) || \is_object($value)) {
                $value = new static($value, $flags, $iterator_class);
            }
            $this[$key] = $value;
        }
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    public function offsetExists($key): bool
    {
        return true;
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (!parent::offsetExists($key)) {
            $this[$key] = new static([], $this->getFlags(), $this->getIteratorClass());
        }

        return parent::offsetGet($key);
    }

    public function getArrayCopy(): array
    {
        $result = parent::getArrayCopy();
        foreach ($result as $key => $value) {
            if ($value instanceof static) {
                $result[$key] = $value->getArrayCopy();
            }
        }

        return $result;
    }
}
