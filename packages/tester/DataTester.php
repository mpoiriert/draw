<?php

namespace Draw\Component\Tester;

use PHPUnit\Framework\Assert;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class DataTester
{
    use AssertTrait;

    /**
     * The root data that the asserts will be done on.
     *
     * @var mixed
     */
    private $data;

    private static ?PropertyAccessorInterface $propertyAccessor = null;

    /**
     * A private static property accessor so we do not need to initialize it more than once.
     */
    protected static function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === self::$propertyAccessor) {
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
     * Return the data value of what is currently tested.
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
     * @param string|PropertyPathInterface $path
     */
    public function path($path): self
    {
        $this->assertPathIsReadable($path);

        return new self(static::getPropertyAccessor()->getValue($this->data, $path));
    }

    /**
     * Execute the callable if the path is readable. Useful to test array|object with optional key|property.
     *
     * @param string|PropertyPathInterface $path
     */
    public function ifPathIsReadable($path, callable $callable): self
    {
        if ($this->isReadable($path)) {
            $callable($this->path($path));
        }

        return $this;
    }

    /**
     * @param string|PropertyPathInterface $path
     */
    public function isReadable($path): bool
    {
        return static::getPropertyAccessor()->isReadable($this->data, $path);
    }

    /**
     * Loop trough the current data and call the callable with a independent tester.
     */
    public function each(callable $callable): self
    {
        foreach ($this->data as $value) {
            $callable(new self($value));
        }

        return $this;
    }

    /**
     * Transform the data and return a new instance of Tester with the transformed data.
     *
     * @param callable $callable The callable that will transform the data
     */
    public function transform(callable $callable): self
    {
        return new self($callable($this->getData()));
    }

    /**
     * @param string|PropertyPathInterface $path
     */
    public function assertPathIsReadable($path, ?string $message = null): self
    {
        Assert::assertTrue(
            $this->isReadable($path),
            $message ?:
                "Property path is not readable.\nProperty path: ".$path."\nData:\n".
                json_encode($this->data, \JSON_PRETTY_PRINT)."\nBe careful for assoc array and object"
        );

        return $this;
    }

    /**
     * @param string|PropertyPathInterface $path
     */
    public function assertPathIsNotReadable($path, ?string $message = null): self
    {
        Assert::assertFalse(
            $this->isReadable($path),
            $message ?:
                "Property path is readable.\nProperty path: ".$path."\nData:\n".
                json_encode($this->data, \JSON_PRETTY_PRINT)."\nBe careful for assoc array and object"
        );

        return $this;
    }

    /**
     * Execute the callable with $this as the parameters.
     * Useful to create reusable test.
     */
    public function test(callable $callable): self
    {
        \call_user_func($callable, $this);

        return $this;
    }

    public static function createCallable(string $methodName, ...$parameters): callable
    {
        return function (self $dataTester) use ($methodName, $parameters) {
            \call_user_func_array([$dataTester, $methodName], array_values($parameters));
        };
    }
}
