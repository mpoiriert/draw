<?php

namespace Draw\Component\Tester;

use Draw\Component\Core\DataAccessor;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class DataTester extends DataAccessor
{
    use AssertTrait;

    /**
     * Execute the callable if the path is readable. Useful to test array|object with optional key|property.
     */
    public function ifPathIsReadable(string|PropertyPathInterface $path, callable $callable): self
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

    public function assertPathIsReadable(string|PropertyPathInterface $path, ?string $message = null): self
    {
        Assert::assertTrue(
            $this->isReadable($path),
            $message ?:
                "Property path is not readable.\nProperty path: ".$path."\nData:\n".
                json_encode($this->getData(), \JSON_PRETTY_PRINT)."\nBe careful for assoc array and object"
        );

        return $this;
    }

    public function assertPathIsNotReadable(string|PropertyPathInterface $path, ?string $message = null): self
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

    public function assertThat(Constraint $constraint, string $message = ''): self
    {
        TestCase::assertThat($this->getData(), $constraint, $message);

        return $this;
    }
}
