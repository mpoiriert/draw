<?php

namespace Draw\Component\Core;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class DataAccessor
{
    private static ?PropertyAccessorInterface $propertyAccessor = null;

    /**
     * A private static property accessor so we do not need to initialize it more than once.
     */
    protected static function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor()
            ;
        }

        return self::$propertyAccessor;
    }

    final public function __construct(
        /**
         * The root data that the asserts will be done on.
         */
        private mixed $data,
    ) {
    }

    /**
     * Return the data value of what is currently tested.
     *
     * @param $path null|string|PropertyPathInterface
     */
    public function getData($path = null)
    {
        return null !== $path ? $this->path($path)->getData() : $this->data;
    }

    /**
     * Transform the data and return a new instance of Tester with the transformed data.
     *
     * @param callable $callable The callable that will transform the data
     *
     * @return static
     */
    public function transform(callable $callable): self
    {
        return new static($callable($this->getData()));
    }

    /**
     * Return a new Tester instance with the path value as data.
     *
     * @param string|PropertyPathInterface $path
     *
     * @return static
     */
    public function path($path): self
    {
        return new static(static::getPropertyAccessor()->getValue($this->data, $path));
    }

    /**
     * @param string|PropertyPathInterface $path
     */
    public function isReadable($path): bool
    {
        return static::getPropertyAccessor()->isReadable($this->getData(), $path);
    }
}
