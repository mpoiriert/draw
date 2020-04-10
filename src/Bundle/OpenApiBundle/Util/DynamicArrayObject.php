<?php namespace Draw\Bundle\OpenApiBundle\Util;

use ArrayObject;

class DynamicArrayObject extends ArrayObject
{
    public function __construct($input,  $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);

        if(is_null($input)) {
            return;
        }

        foreach($input as $key => $value) {
            if(is_array($value) || is_object($value)) {
                $value = new static($value, $flags, $iterator_class);
            }
            $this[$key] = $value;
        }
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!parent::offsetExists($offset)) {
            $this[$offset] = new static(array(), $this->getFlags(), $this->getIteratorClass());
        }

        return parent::offsetGet($offset);
    }

    public function getArrayCopy()
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