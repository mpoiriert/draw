<?php

namespace Draw\DataTester;

use Symfony\Component\PropertyAccess\PropertyAccess;

use PHPUnit\Framework\Assert;

class Tester
{
    use AssertTrait;

    /**
     * The root data that the asserts will be done on
     *
     * @var mixed
     */
    private $data;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private static $propertyAccessor;

    /**
     * A private static property accessor so we do not need to initialize it more than once
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    protected static function getPropertyAccessor()
    {
        if (is_null(self::$propertyAccessor)) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        }
        return self::$propertyAccessor;
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return the data value of what is currently tested
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return a new Tester instance with the path value as data.
     *
     * @see http://symfony.com/doc/2.3/components/property_access/introduction.html
     *
     * @param string $path path compatible with Symfony\Component\PropertyAccess\PropertyAccessor
     *
     * @return static
     */
    public function path($path)
    {
        $this->assertPathIsReadable($path);

        return new static(static::getPropertyAccessor()->getValue($this->data, $path));
    }

    /**
     * Execute the callable if the path is readable. Useful to test array|object with optional key|property.
     *
     *
     *
     * @param $path
     * @param callable $callable
     * @return $this
     */
    public function ifPathIsReadable($path, callable $callable)
    {
        if ($this->isReadable($path)) {
            $callable($this->path($path));
        }

        return $this;
    }

    /**
     * Check if a path is readable
     *
     * @param $path
     * @return bool
     */
    public function isReadable($path)
    {
        return static::getPropertyAccessor()->isReadable($this->data, $path);
    }

    /**
     * Loop trough the current data and call the callable with a independent tester
     *
     * @param callable $callable
     * @return $this
     */
    public function each(callable $callable)
    {
        foreach ($this->data as $value) {
            $callable(new static($value));
        }

        return $this;
    }

    /**
     * Transform the data and return a new instance of Tester with the transformed data.
     *
     * @param callable $callable The callable that will transform the data
     * @return $this
     */
    public function transform(callable $callable)
    {
        return new static($callable($this->getData()));
    }

    /**
     * @param $path
     * @param string $message
     * @return $this
     */
    public function assertPathIsReadable($path, $message = null)
    {
        Assert::assertTrue(
            $this->isReadable($path),
            $message ?:
                "Property path is not readable.\nProperty path: " . $path . "\nData:\n" .
                json_encode($this->data, JSON_PRETTY_PRINT) . "\nBe careful for assoc array and object"

        );

        return $this;
    }

    /**
     * @param $path
     * @param null $message
     * @return $this
     */
    public function assertPathIsNotReadable($path, $message = null)
    {
        Assert::assertFalse(
            $this->isReadable($path),
            $message ?:
                "Property path is readable.\nProperty path: " . $path . "\nData:\n" .
                json_encode($this->data, JSON_PRETTY_PRINT) . "\nBe careful for assoc array and object"
        );

        return $this;
    }

    /**
     * Execute the callable with $this as the parameters.
     * Useful to create reusable test.
     *
     * @param callable $callable
     * @return $this
     */
    public function test(callable $callable)
    {
        call_user_func($callable, $this);

        return $this;
    }
}