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
    static private $propertyAccessor;

    /**
     * @var \ReflectionClass
     */
    static private $assertReflectionClass;

    /**
     * A private static property accessor so we do not need to initialize it more than once
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    static protected function getPropertyAccessor()
    {
        if (is_null(static::$propertyAccessor)) {
            static::$propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        }
        return static::$propertyAccessor;
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
     * @see http://symfony.com/doc/2.3/components/property_access/introduction.html
     *
     * @param string $path path compatible with Symfony\Component\PropertyAccess\PropertyAccessor
     * @param $callable A callable that will receive a tester with the value path for data.
     *
     * @return static
     */
    public function path($path, $callable = null)
    {
        $this->assertPathIsReadable($path);

        if ($callable) {
            call_user_func(
                $callable,
                new static(static::getPropertyAccessor()->getValue($this->data, $path))
            );
        }

        return $this;
    }

    /**
     * Execute the callable if the path is readable. Useful to test array|object with optional key|property.
     *
     * @param $path
     * @param $callable
     * @return $this
     */
    public function ifPathIsReadable($path, $callable)
    {
        if ($this->isReadable($path)) {
            $this->path($path, $callable);
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
     * @param $callable
     * @return $this
     */
    public function each($callable)
    {
        foreach ($this->data as $value) {
            call_user_func(
                $callable,
                new static($value)
            );
        }

        return $this;
    }

    /**
     * @param callable $transformCallable The callable that will transform the data
     * @param callable $callable The callable that will be call with the new tester
     * @return $this
     */
    public function transform(callable $transformCallable, callable $callable)
    {
        call_user_func(
            $transformCallable,
            call_user_func(
                $callable,
                new static(call_user_func($transformCallable, $this->getData()))
            )
        );

        return $this;
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

    /**
     * @return \ReflectionClass
     */
    private static function getAssertReflectionClass()
    {
        if (is_null(static::$assertReflectionClass)) {
            static::$assertReflectionClass = new \ReflectionClass(TestCase::class);
        }

        return static::$assertReflectionClass;
    }
}