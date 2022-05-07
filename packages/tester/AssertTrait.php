<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-trait.php script.
 * Do not modify manually.
 */

namespace Draw\Component\Tester;

use ArrayAccess;
use Countable;
use PHPUnit\Framework\Assert;
use Traversable;

/**
 * @internal
 */
trait AssertTrait
{
    /**
     * @return mixed Return the data that is currently tested
     */
    abstract public function getData();

    // example-start: assertArraySubset

    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess $subset
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3494
     *
     * @return $this
     */
    public function assertArraySubset($subset, bool $checkForObjectIdentity = false, string $message = '')
    {
        Assert::assertArraySubset($subset, $this->getData(), $checkForObjectIdentity, $message);

        return $this;
    }

    // example-end: assertArraySubset

    // example-start: assertContains

    /**
     * Asserts that a haystack contains a needle.
     *
     * @return $this
     */
    public function assertContains($needle, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false)
    {
        Assert::assertContains($needle, $this->getData(), $message, $ignoreCase, $checkForObjectIdentity, $checkForNonObjectIdentity);

        return $this;
    }

    // example-end: assertContains

    // example-start: assertNotContains

    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @return $this
     */
    public function assertNotContains($needle, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false)
    {
        Assert::assertNotContains($needle, $this->getData(), $message, $ignoreCase, $checkForObjectIdentity, $checkForNonObjectIdentity);

        return $this;
    }

    // example-end: assertNotContains

    // example-start: assertContainsOnly

    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @return $this
     */
    public function assertContainsOnly(string $type, bool $isNativeType = null, string $message = '')
    {
        Assert::assertContainsOnly($type, $this->getData(), $isNativeType, $message);

        return $this;
    }

    // example-end: assertContainsOnly

    // example-start: assertContainsOnlyInstancesOf

    /**
     * Asserts that a haystack contains only instances of a given class name.
     *
     * @return $this
     */
    public function assertContainsOnlyInstancesOf(string $className, string $message = '')
    {
        Assert::assertContainsOnlyInstancesOf($className, $this->getData(), $message);

        return $this;
    }

    // example-end: assertContainsOnlyInstancesOf

    // example-start: assertNotContainsOnly

    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @return $this
     */
    public function assertNotContainsOnly(string $type, bool $isNativeType = null, string $message = '')
    {
        Assert::assertNotContainsOnly($type, $this->getData(), $isNativeType, $message);

        return $this;
    }

    // example-end: assertNotContainsOnly

    // example-start: assertCount

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @return $this
     */
    public function assertCount(int $expectedCount, string $message = '')
    {
        Assert::assertCount($expectedCount, $this->getData(), $message);

        return $this;
    }

    // example-end: assertCount

    // example-start: assertNotCount

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @return $this
     */
    public function assertNotCount(int $expectedCount, string $message = '')
    {
        Assert::assertNotCount($expectedCount, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotCount

    // example-start: assertEquals

    /**
     * Asserts that two variables are equal.
     *
     * @return $this
     */
    public function assertEquals($expected, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false)
    {
        Assert::assertEquals($expected, $this->getData(), $message, $delta, $maxDepth, $canonicalize, $ignoreCase);

        return $this;
    }

    // example-end: assertEquals

    // example-start: assertNotEquals

    /**
     * Asserts that two variables are not equal.
     *
     * @param float $delta
     * @param int   $maxDepth
     * @param bool  $canonicalize
     * @param bool  $ignoreCase
     *
     * @return $this
     */
    public function assertNotEquals($expected, string $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        Assert::assertNotEquals($expected, $this->getData(), $message, $delta, $maxDepth, $canonicalize, $ignoreCase);

        return $this;
    }

    // example-end: assertNotEquals

    // example-start: assertEmpty

    /**
     * Asserts that a variable is empty.
     *
     * @psalm-assert empty $actual
     *
     * @return $this
     */
    public function assertEmpty(string $message = '')
    {
        Assert::assertEmpty($this->getData(), $message);

        return $this;
    }

    // example-end: assertEmpty

    // example-start: assertNotEmpty

    /**
     * Asserts that a variable is not empty.
     *
     * @psalm-assert !empty $actual
     *
     * @return $this
     */
    public function assertNotEmpty(string $message = '')
    {
        Assert::assertNotEmpty($this->getData(), $message);

        return $this;
    }

    // example-end: assertNotEmpty

    // example-start: assertGreaterThan

    /**
     * Asserts that a value is greater than another value.
     *
     * @return $this
     */
    public function assertGreaterThan($expected, string $message = '')
    {
        Assert::assertGreaterThan($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertGreaterThan

    // example-start: assertGreaterThanOrEqual

    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @return $this
     */
    public function assertGreaterThanOrEqual($expected, string $message = '')
    {
        Assert::assertGreaterThanOrEqual($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertGreaterThanOrEqual

    // example-start: assertLessThan

    /**
     * Asserts that a value is smaller than another value.
     *
     * @return $this
     */
    public function assertLessThan($expected, string $message = '')
    {
        Assert::assertLessThan($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertLessThan

    // example-start: assertLessThanOrEqual

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @return $this
     */
    public function assertLessThanOrEqual($expected, string $message = '')
    {
        Assert::assertLessThanOrEqual($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertLessThanOrEqual

    // example-start: assertTrue

    /**
     * Asserts that a condition is true.
     *
     * @psalm-assert true $condition
     *
     * @return $this
     */
    public function assertTrue(string $message = '')
    {
        Assert::assertTrue($this->getData(), $message);

        return $this;
    }

    // example-end: assertTrue

    // example-start: assertNotTrue

    /**
     * Asserts that a condition is not true.
     *
     * @psalm-assert !true $condition
     *
     * @return $this
     */
    public function assertNotTrue(string $message = '')
    {
        Assert::assertNotTrue($this->getData(), $message);

        return $this;
    }

    // example-end: assertNotTrue

    // example-start: assertFalse

    /**
     * Asserts that a condition is false.
     *
     * @psalm-assert false $condition
     *
     * @return $this
     */
    public function assertFalse(string $message = '')
    {
        Assert::assertFalse($this->getData(), $message);

        return $this;
    }

    // example-end: assertFalse

    // example-start: assertNotFalse

    /**
     * Asserts that a condition is not false.
     *
     * @psalm-assert !false $condition
     *
     * @return $this
     */
    public function assertNotFalse(string $message = '')
    {
        Assert::assertNotFalse($this->getData(), $message);

        return $this;
    }

    // example-end: assertNotFalse

    // example-start: assertNull

    /**
     * Asserts that a variable is null.
     *
     * @psalm-assert null $actual
     *
     * @return $this
     */
    public function assertNull(string $message = '')
    {
        Assert::assertNull($this->getData(), $message);

        return $this;
    }

    // example-end: assertNull

    // example-start: assertNotNull

    /**
     * Asserts that a variable is not null.
     *
     * @psalm-assert !null $actual
     *
     * @return $this
     */
    public function assertNotNull(string $message = '')
    {
        Assert::assertNotNull($this->getData(), $message);

        return $this;
    }

    // example-end: assertNotNull

    // example-start: assertFinite

    /**
     * Asserts that a variable is finite.
     *
     * @return $this
     */
    public function assertFinite(string $message = '')
    {
        Assert::assertFinite($this->getData(), $message);

        return $this;
    }

    // example-end: assertFinite

    // example-start: assertInfinite

    /**
     * Asserts that a variable is infinite.
     *
     * @return $this
     */
    public function assertInfinite(string $message = '')
    {
        Assert::assertInfinite($this->getData(), $message);

        return $this;
    }

    // example-end: assertInfinite

    // example-start: assertNan

    /**
     * Asserts that a variable is nan.
     *
     * @return $this
     */
    public function assertNan(string $message = '')
    {
        Assert::assertNan($this->getData(), $message);

        return $this;
    }

    // example-end: assertNan

    // example-start: assertSame

    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @psalm-template ExpectedType
     * @psalm-param ExpectedType $expected
     * @psalm-assert =ExpectedType $actual
     *
     * @return $this
     */
    public function assertSame($expected, string $message = '')
    {
        Assert::assertSame($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertSame

    // example-start: assertNotSame

    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @return $this
     */
    public function assertNotSame($expected, string $message = '')
    {
        Assert::assertNotSame($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotSame

    // example-start: assertInstanceOf

    /**
     * Asserts that a variable is of a given type.
     *
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $expected
     * @psalm-assert ExpectedType $actual
     *
     * @return $this
     */
    public function assertInstanceOf(string $expected, string $message = '')
    {
        Assert::assertInstanceOf($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertInstanceOf

    // example-start: assertNotInstanceOf

    /**
     * Asserts that a variable is not of a given type.
     *
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $expected
     * @psalm-assert !ExpectedType $actual
     *
     * @return $this
     */
    public function assertNotInstanceOf(string $expected, string $message = '')
    {
        Assert::assertNotInstanceOf($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotInstanceOf

    // example-start: assertInternalType

    /**
     * Asserts that a variable is of a given type.
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3369
     * @codeCoverageIgnore
     *
     * @return $this
     */
    public function assertInternalType(string $expected, string $message = '')
    {
        Assert::assertInternalType($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertInternalType

    // example-start: assertNotInternalType

    /**
     * Asserts that a variable is not of a given type.
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3369
     * @codeCoverageIgnore
     *
     * @return $this
     */
    public function assertNotInternalType(string $expected, string $message = '')
    {
        Assert::assertNotInternalType($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotInternalType

    // example-start: assertRegExp

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @return $this
     */
    public function assertRegExp(string $pattern, string $message = '')
    {
        Assert::assertRegExp($pattern, $this->getData(), $message);

        return $this;
    }

    // example-end: assertRegExp

    // example-start: assertNotRegExp

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @return $this
     */
    public function assertNotRegExp(string $pattern, string $message = '')
    {
        Assert::assertNotRegExp($pattern, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotRegExp

    // example-start: assertSameSize

    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param Countable|iterable $expected
     *
     * @return $this
     */
    public function assertSameSize($expected, string $message = '')
    {
        Assert::assertSameSize($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertSameSize

    // example-start: assertNotSameSize

    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is not the same.
     *
     * @param Countable|iterable $expected
     *
     * @return $this
     */
    public function assertNotSameSize($expected, string $message = '')
    {
        Assert::assertNotSameSize($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotSameSize

    // example-start: assertStringMatchesFormat

    /**
     * Asserts that a string matches a given format string.
     *
     * @return $this
     */
    public function assertStringMatchesFormat(string $format, string $message = '')
    {
        Assert::assertStringMatchesFormat($format, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringMatchesFormat

    // example-start: assertStringNotMatchesFormat

    /**
     * Asserts that a string does not match a given format string.
     *
     * @return $this
     */
    public function assertStringNotMatchesFormat(string $format, string $message = '')
    {
        Assert::assertStringNotMatchesFormat($format, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringNotMatchesFormat

    // example-start: assertStringStartsWith

    /**
     * Asserts that a string starts with a given prefix.
     *
     * @return $this
     */
    public function assertStringStartsWith(string $prefix, string $message = '')
    {
        Assert::assertStringStartsWith($prefix, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringStartsWith

    // example-start: assertStringStartsNotWith

    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function assertStringStartsNotWith($prefix, string $message = '')
    {
        Assert::assertStringStartsNotWith($prefix, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringStartsNotWith

    // example-start: assertStringEndsWith

    /**
     * Asserts that a string ends with a given suffix.
     *
     * @return $this
     */
    public function assertStringEndsWith(string $suffix, string $message = '')
    {
        Assert::assertStringEndsWith($suffix, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringEndsWith

    // example-start: assertStringEndsNotWith

    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @return $this
     */
    public function assertStringEndsNotWith(string $suffix, string $message = '')
    {
        Assert::assertStringEndsNotWith($suffix, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringEndsNotWith

    // example-start: assertJson

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @return $this
     */
    public function assertJson(string $message = '')
    {
        Assert::assertJson($this->getData(), $message);

        return $this;
    }

    // example-end: assertJson

    // example-start: assertJsonStringEqualsJsonString

    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @return $this
     */
    public function assertJsonStringEqualsJsonString(string $expectedJson, string $message = '')
    {
        Assert::assertJsonStringEqualsJsonString($expectedJson, $this->getData(), $message);

        return $this;
    }

    // example-end: assertJsonStringEqualsJsonString

    // example-start: assertJsonStringNotEqualsJsonString

    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     *
     * @return $this
     */
    public function assertJsonStringNotEqualsJsonString($expectedJson, string $message = '')
    {
        Assert::assertJsonStringNotEqualsJsonString($expectedJson, $this->getData(), $message);

        return $this;
    }

    // example-end: assertJsonStringNotEqualsJsonString

    // example-start: assertContainsEquals

    /**
     * @return $this
     */
    public function assertContainsEquals($needle, string $message = '')
    {
        Assert::assertContainsEquals($needle, $this->getData(), $message);

        return $this;
    }

    // example-end: assertContainsEquals

    // example-start: assertNotContainsEquals

    /**
     * @return $this
     */
    public function assertNotContainsEquals($needle, string $message = '')
    {
        Assert::assertNotContainsEquals($needle, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotContainsEquals

    // example-start: assertEqualsCanonicalizing

    /**
     * Asserts that two variables are equal (canonicalizing).
     *
     * @return $this
     */
    public function assertEqualsCanonicalizing($expected, string $message = '')
    {
        Assert::assertEqualsCanonicalizing($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertEqualsCanonicalizing

    // example-start: assertEqualsIgnoringCase

    /**
     * Asserts that two variables are equal (ignoring case).
     *
     * @return $this
     */
    public function assertEqualsIgnoringCase($expected, string $message = '')
    {
        Assert::assertEqualsIgnoringCase($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertEqualsIgnoringCase

    // example-start: assertEqualsWithDelta

    /**
     * Asserts that two variables are equal (with delta).
     *
     * @return $this
     */
    public function assertEqualsWithDelta($expected, float $delta, string $message = '')
    {
        Assert::assertEqualsWithDelta($expected, $this->getData(), $delta, $message);

        return $this;
    }

    // example-end: assertEqualsWithDelta

    // example-start: assertNotEqualsCanonicalizing

    /**
     * Asserts that two variables are not equal (canonicalizing).
     *
     * @return $this
     */
    public function assertNotEqualsCanonicalizing($expected, string $message = '')
    {
        Assert::assertNotEqualsCanonicalizing($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotEqualsCanonicalizing

    // example-start: assertNotEqualsIgnoringCase

    /**
     * Asserts that two variables are not equal (ignoring case).
     *
     * @return $this
     */
    public function assertNotEqualsIgnoringCase($expected, string $message = '')
    {
        Assert::assertNotEqualsIgnoringCase($expected, $this->getData(), $message);

        return $this;
    }

    // example-end: assertNotEqualsIgnoringCase

    // example-start: assertNotEqualsWithDelta

    /**
     * Asserts that two variables are not equal (with delta).
     *
     * @return $this
     */
    public function assertNotEqualsWithDelta($expected, float $delta, string $message = '')
    {
        Assert::assertNotEqualsWithDelta($expected, $this->getData(), $delta, $message);

        return $this;
    }

    // example-end: assertNotEqualsWithDelta

    // example-start: assertIsArray

    /**
     * Asserts that a variable is of type array.
     *
     * @psalm-assert array $actual
     *
     * @return $this
     */
    public function assertIsArray(string $message = '')
    {
        Assert::assertIsArray($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsArray

    // example-start: assertIsBool

    /**
     * Asserts that a variable is of type bool.
     *
     * @psalm-assert bool $actual
     *
     * @return $this
     */
    public function assertIsBool(string $message = '')
    {
        Assert::assertIsBool($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsBool

    // example-start: assertIsFloat

    /**
     * Asserts that a variable is of type float.
     *
     * @psalm-assert float $actual
     *
     * @return $this
     */
    public function assertIsFloat(string $message = '')
    {
        Assert::assertIsFloat($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsFloat

    // example-start: assertIsInt

    /**
     * Asserts that a variable is of type int.
     *
     * @psalm-assert int $actual
     *
     * @return $this
     */
    public function assertIsInt(string $message = '')
    {
        Assert::assertIsInt($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsInt

    // example-start: assertIsNumeric

    /**
     * Asserts that a variable is of type numeric.
     *
     * @psalm-assert numeric $actual
     *
     * @return $this
     */
    public function assertIsNumeric(string $message = '')
    {
        Assert::assertIsNumeric($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNumeric

    // example-start: assertIsObject

    /**
     * Asserts that a variable is of type object.
     *
     * @psalm-assert object $actual
     *
     * @return $this
     */
    public function assertIsObject(string $message = '')
    {
        Assert::assertIsObject($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsObject

    // example-start: assertIsResource

    /**
     * Asserts that a variable is of type resource.
     *
     * @psalm-assert resource $actual
     *
     * @return $this
     */
    public function assertIsResource(string $message = '')
    {
        Assert::assertIsResource($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsResource

    // example-start: assertIsString

    /**
     * Asserts that a variable is of type string.
     *
     * @psalm-assert string $actual
     *
     * @return $this
     */
    public function assertIsString(string $message = '')
    {
        Assert::assertIsString($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsString

    // example-start: assertIsScalar

    /**
     * Asserts that a variable is of type scalar.
     *
     * @psalm-assert scalar $actual
     *
     * @return $this
     */
    public function assertIsScalar(string $message = '')
    {
        Assert::assertIsScalar($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsScalar

    // example-start: assertIsCallable

    /**
     * Asserts that a variable is of type callable.
     *
     * @psalm-assert callable $actual
     *
     * @return $this
     */
    public function assertIsCallable(string $message = '')
    {
        Assert::assertIsCallable($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsCallable

    // example-start: assertIsIterable

    /**
     * Asserts that a variable is of type iterable.
     *
     * @psalm-assert iterable $actual
     *
     * @return $this
     */
    public function assertIsIterable(string $message = '')
    {
        Assert::assertIsIterable($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsIterable

    // example-start: assertIsNotArray

    /**
     * Asserts that a variable is not of type array.
     *
     * @psalm-assert !array $actual
     *
     * @return $this
     */
    public function assertIsNotArray(string $message = '')
    {
        Assert::assertIsNotArray($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotArray

    // example-start: assertIsNotBool

    /**
     * Asserts that a variable is not of type bool.
     *
     * @psalm-assert !bool $actual
     *
     * @return $this
     */
    public function assertIsNotBool(string $message = '')
    {
        Assert::assertIsNotBool($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotBool

    // example-start: assertIsNotFloat

    /**
     * Asserts that a variable is not of type float.
     *
     * @psalm-assert !float $actual
     *
     * @return $this
     */
    public function assertIsNotFloat(string $message = '')
    {
        Assert::assertIsNotFloat($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotFloat

    // example-start: assertIsNotInt

    /**
     * Asserts that a variable is not of type int.
     *
     * @psalm-assert !int $actual
     *
     * @return $this
     */
    public function assertIsNotInt(string $message = '')
    {
        Assert::assertIsNotInt($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotInt

    // example-start: assertIsNotNumeric

    /**
     * Asserts that a variable is not of type numeric.
     *
     * @psalm-assert !numeric $actual
     *
     * @return $this
     */
    public function assertIsNotNumeric(string $message = '')
    {
        Assert::assertIsNotNumeric($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotNumeric

    // example-start: assertIsNotObject

    /**
     * Asserts that a variable is not of type object.
     *
     * @psalm-assert !object $actual
     *
     * @return $this
     */
    public function assertIsNotObject(string $message = '')
    {
        Assert::assertIsNotObject($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotObject

    // example-start: assertIsNotResource

    /**
     * Asserts that a variable is not of type resource.
     *
     * @psalm-assert !resource $actual
     *
     * @return $this
     */
    public function assertIsNotResource(string $message = '')
    {
        Assert::assertIsNotResource($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotResource

    // example-start: assertIsNotString

    /**
     * Asserts that a variable is not of type string.
     *
     * @psalm-assert !string $actual
     *
     * @return $this
     */
    public function assertIsNotString(string $message = '')
    {
        Assert::assertIsNotString($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotString

    // example-start: assertIsNotScalar

    /**
     * Asserts that a variable is not of type scalar.
     *
     * @psalm-assert !scalar $actual
     *
     * @return $this
     */
    public function assertIsNotScalar(string $message = '')
    {
        Assert::assertIsNotScalar($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotScalar

    // example-start: assertIsNotCallable

    /**
     * Asserts that a variable is not of type callable.
     *
     * @psalm-assert !callable $actual
     *
     * @return $this
     */
    public function assertIsNotCallable(string $message = '')
    {
        Assert::assertIsNotCallable($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotCallable

    // example-start: assertIsNotIterable

    /**
     * Asserts that a variable is not of type iterable.
     *
     * @psalm-assert !iterable $actual
     *
     * @return $this
     */
    public function assertIsNotIterable(string $message = '')
    {
        Assert::assertIsNotIterable($this->getData(), $message);

        return $this;
    }

    // example-end: assertIsNotIterable

    // example-start: assertStringContainsString

    /**
     * @return $this
     */
    public function assertStringContainsString(string $needle, string $message = '')
    {
        Assert::assertStringContainsString($needle, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringContainsString

    // example-start: assertStringContainsStringIgnoringCase

    /**
     * @return $this
     */
    public function assertStringContainsStringIgnoringCase(string $needle, string $message = '')
    {
        Assert::assertStringContainsStringIgnoringCase($needle, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringContainsStringIgnoringCase

    // example-start: assertStringNotContainsString

    /**
     * @return $this
     */
    public function assertStringNotContainsString(string $needle, string $message = '')
    {
        Assert::assertStringNotContainsString($needle, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringNotContainsString

    // example-start: assertStringNotContainsStringIgnoringCase

    /**
     * @return $this
     */
    public function assertStringNotContainsStringIgnoringCase(string $needle, string $message = '')
    {
        Assert::assertStringNotContainsStringIgnoringCase($needle, $this->getData(), $message);

        return $this;
    }

    // example-end: assertStringNotContainsStringIgnoringCase
}
