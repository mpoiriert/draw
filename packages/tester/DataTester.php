<?php

namespace Draw\Component\Tester;

use Draw\Component\Core\DataAccessor;
use PHPUnit\Framework\Assert;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class DataTester extends DataAccessor
{
    use AssertTrait;

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
        return static::getPropertyAccessor()->isReadable($this->getData(), $path);
    }

    /**
     * Loop trough the current data and call the callable with a independent tester.
     */
    public function each(callable $callable): self
    {
        foreach ($this->getData() as $value) {
            $callable(new static($value));
        }

        return $this;
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
                json_encode($this->getData(), \JSON_PRETTY_PRINT)."\nBe careful for assoc array and object"
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
                json_encode($this->getData(), \JSON_PRETTY_PRINT)."\nBe careful for assoc array and object"
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

    /**
     * @param mixed ...$parameters
     */
    public static function createCallable(string $methodName, ...$parameters): callable
    {
        return function (self $dataTester) use ($methodName, $parameters): void {
            \call_user_func_array([$dataTester, $methodName], array_values($parameters));
        };
    }
}
