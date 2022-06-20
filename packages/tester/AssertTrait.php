<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-trait.php script.
 * Do not modify manually.
 */

namespace Draw\Component\Tester;

use PHPUnit\Framework\Assert;

/**
 * @internal
 */
trait AssertTrait
{
    /**
     * @return mixed Return the data that is currently tested
     */
    abstract public function getData();

    // example-start: assertContains
    /**
     * Asserts that a haystack contains a needle.
     *
     * @param mixed $needle
     */
    public function assertContains($needle, string $message = ''): self
    {
        Assert::assertContains($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertContains

    // example-start: assertNotContains
    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param mixed $needle
     */
    public function assertNotContains($needle, string $message = ''): self
    {
        Assert::assertNotContains($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotContains

    // example-start: assertContainsOnly
    /**
     * Asserts that a haystack contains only values of a given type.
     */
    public function assertContainsOnly(string $type, ?bool $isNativeType = null, string $message = ''): self
    {
        Assert::assertContainsOnly($type, $this->getData(), $isNativeType, $message);

        return $this;
    }
    // example-end: assertContainsOnly

    // example-start: assertContainsOnlyInstancesOf
    /**
     * Asserts that a haystack contains only instances of a given class name.
     */
    public function assertContainsOnlyInstancesOf(string $className, string $message = ''): self
    {
        Assert::assertContainsOnlyInstancesOf($className, $this->getData(), $message);

        return $this;
    }
    // example-end: assertContainsOnlyInstancesOf

    // example-start: assertNotContainsOnly
    /**
     * Asserts that a haystack does not contain only values of a given type.
     */
    public function assertNotContainsOnly(string $type, ?bool $isNativeType = null, string $message = ''): self
    {
        Assert::assertNotContainsOnly($type, $this->getData(), $isNativeType, $message);

        return $this;
    }
    // example-end: assertNotContainsOnly

    // example-start: assertCount
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     */
    public function assertCount(int $expectedCount, string $message = ''): self
    {
        Assert::assertCount($expectedCount, $this->getData(), $message);

        return $this;
    }
    // example-end: assertCount

    // example-start: assertNotCount
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     */
    public function assertNotCount(int $expectedCount, string $message = ''): self
    {
        Assert::assertNotCount($expectedCount, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotCount

    // example-start: assertEquals
    /**
     * Asserts that two variables are equal.
     *
     * @param mixed $expected
     */
    public function assertEquals($expected, string $message = ''): self
    {
        Assert::assertEquals($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertEquals

    // example-start: assertNotEquals
    /**
     * Asserts that two variables are not equal.
     *
     * @param mixed $expected
     */
    public function assertNotEquals($expected, string $message = ''): self
    {
        Assert::assertNotEquals($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotEquals

    // example-start: assertEmpty
    /**
     * Asserts that a variable is empty.
     */
    public function assertEmpty(string $message = ''): self
    {
        Assert::assertEmpty($this->getData(), $message);

        return $this;
    }
    // example-end: assertEmpty

    // example-start: assertNotEmpty
    /**
     * Asserts that a variable is not empty.
     */
    public function assertNotEmpty(string $message = ''): self
    {
        Assert::assertNotEmpty($this->getData(), $message);

        return $this;
    }
    // example-end: assertNotEmpty

    // example-start: assertGreaterThan
    /**
     * Asserts that a value is greater than another value.
     *
     * @param mixed $expected
     */
    public function assertGreaterThan($expected, string $message = ''): self
    {
        Assert::assertGreaterThan($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertGreaterThan

    // example-start: assertGreaterThanOrEqual
    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @param mixed $expected
     */
    public function assertGreaterThanOrEqual($expected, string $message = ''): self
    {
        Assert::assertGreaterThanOrEqual($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertGreaterThanOrEqual

    // example-start: assertLessThan
    /**
     * Asserts that a value is smaller than another value.
     *
     * @param mixed $expected
     */
    public function assertLessThan($expected, string $message = ''): self
    {
        Assert::assertLessThan($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertLessThan

    // example-start: assertLessThanOrEqual
    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @param mixed $expected
     */
    public function assertLessThanOrEqual($expected, string $message = ''): self
    {
        Assert::assertLessThanOrEqual($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertLessThanOrEqual

    // example-start: assertTrue
    /**
     * Asserts that a condition is true.
     */
    public function assertTrue(string $message = ''): self
    {
        Assert::assertTrue($this->getData(), $message);

        return $this;
    }
    // example-end: assertTrue

    // example-start: assertNotTrue
    /**
     * Asserts that a condition is not true.
     */
    public function assertNotTrue(string $message = ''): self
    {
        Assert::assertNotTrue($this->getData(), $message);

        return $this;
    }
    // example-end: assertNotTrue

    // example-start: assertFalse
    /**
     * Asserts that a condition is false.
     */
    public function assertFalse(string $message = ''): self
    {
        Assert::assertFalse($this->getData(), $message);

        return $this;
    }
    // example-end: assertFalse

    // example-start: assertNotFalse
    /**
     * Asserts that a condition is not false.
     */
    public function assertNotFalse(string $message = ''): self
    {
        Assert::assertNotFalse($this->getData(), $message);

        return $this;
    }
    // example-end: assertNotFalse

    // example-start: assertNull
    /**
     * Asserts that a variable is null.
     */
    public function assertNull(string $message = ''): self
    {
        Assert::assertNull($this->getData(), $message);

        return $this;
    }
    // example-end: assertNull

    // example-start: assertNotNull
    /**
     * Asserts that a variable is not null.
     */
    public function assertNotNull(string $message = ''): self
    {
        Assert::assertNotNull($this->getData(), $message);

        return $this;
    }
    // example-end: assertNotNull

    // example-start: assertFinite
    /**
     * Asserts that a variable is finite.
     */
    public function assertFinite(string $message = ''): self
    {
        Assert::assertFinite($this->getData(), $message);

        return $this;
    }
    // example-end: assertFinite

    // example-start: assertInfinite
    /**
     * Asserts that a variable is infinite.
     */
    public function assertInfinite(string $message = ''): self
    {
        Assert::assertInfinite($this->getData(), $message);

        return $this;
    }
    // example-end: assertInfinite

    // example-start: assertNan
    /**
     * Asserts that a variable is nan.
     */
    public function assertNan(string $message = ''): self
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
     * @param mixed $expected
     */
    public function assertSame($expected, string $message = ''): self
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
     * @param mixed $expected
     */
    public function assertNotSame($expected, string $message = ''): self
    {
        Assert::assertNotSame($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotSame

    // example-start: assertInstanceOf
    /**
     * Asserts that a variable is of a given type.
     */
    public function assertInstanceOf(string $expected, string $message = ''): self
    {
        Assert::assertInstanceOf($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertInstanceOf

    // example-start: assertNotInstanceOf
    /**
     * Asserts that a variable is not of a given type.
     */
    public function assertNotInstanceOf(string $expected, string $message = ''): self
    {
        Assert::assertNotInstanceOf($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotInstanceOf

    // example-start: assertSameSize
    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param \Countable|iterable $expected
     */
    public function assertSameSize($expected, string $message = ''): self
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
     * @param \Countable|iterable $expected
     */
    public function assertNotSameSize($expected, string $message = ''): self
    {
        Assert::assertNotSameSize($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotSameSize

    // example-start: assertStringMatchesFormat
    /**
     * Asserts that a string matches a given format string.
     */
    public function assertStringMatchesFormat(string $format, string $message = ''): self
    {
        Assert::assertStringMatchesFormat($format, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringMatchesFormat

    // example-start: assertStringNotMatchesFormat
    /**
     * Asserts that a string does not match a given format string.
     */
    public function assertStringNotMatchesFormat(string $format, string $message = ''): self
    {
        Assert::assertStringNotMatchesFormat($format, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringNotMatchesFormat

    // example-start: assertStringStartsWith
    /**
     * Asserts that a string starts with a given prefix.
     */
    public function assertStringStartsWith(string $prefix, string $message = ''): self
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
     */
    public function assertStringStartsNotWith($prefix, string $message = ''): self
    {
        Assert::assertStringStartsNotWith($prefix, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringStartsNotWith

    // example-start: assertStringEndsWith
    /**
     * Asserts that a string ends with a given suffix.
     */
    public function assertStringEndsWith(string $suffix, string $message = ''): self
    {
        Assert::assertStringEndsWith($suffix, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringEndsWith

    // example-start: assertStringEndsNotWith
    /**
     * Asserts that a string ends not with a given suffix.
     */
    public function assertStringEndsNotWith(string $suffix, string $message = ''): self
    {
        Assert::assertStringEndsNotWith($suffix, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringEndsNotWith

    // example-start: assertJson
    /**
     * Asserts that a string is a valid JSON string.
     */
    public function assertJson(string $message = ''): self
    {
        Assert::assertJson($this->getData(), $message);

        return $this;
    }
    // example-end: assertJson

    // example-start: assertJsonStringEqualsJsonString
    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     */
    public function assertJsonStringEqualsJsonString(string $expectedJson, string $message = ''): self
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
     */
    public function assertJsonStringNotEqualsJsonString($expectedJson, string $message = ''): self
    {
        Assert::assertJsonStringNotEqualsJsonString($expectedJson, $this->getData(), $message);

        return $this;
    }
    // example-end: assertJsonStringNotEqualsJsonString

    // example-start: assertContainsEquals

    public function assertContainsEquals($needle, string $message = ''): self
    {
        Assert::assertContainsEquals($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertContainsEquals

    // example-start: assertNotContainsEquals

    public function assertNotContainsEquals($needle, string $message = ''): self
    {
        Assert::assertNotContainsEquals($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotContainsEquals

    // example-start: assertEqualsCanonicalizing
    /**
     * Asserts that two variables are equal (canonicalizing).
     *
     * @param mixed $expected
     */
    public function assertEqualsCanonicalizing($expected, string $message = ''): self
    {
        Assert::assertEqualsCanonicalizing($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertEqualsCanonicalizing

    // example-start: assertEqualsIgnoringCase
    /**
     * Asserts that two variables are equal (ignoring case).
     *
     * @param mixed $expected
     */
    public function assertEqualsIgnoringCase($expected, string $message = ''): self
    {
        Assert::assertEqualsIgnoringCase($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertEqualsIgnoringCase

    // example-start: assertEqualsWithDelta
    /**
     * Asserts that two variables are equal (with delta).
     *
     * @param mixed $expected
     */
    public function assertEqualsWithDelta($expected, float $delta, string $message = ''): self
    {
        Assert::assertEqualsWithDelta($expected, $this->getData(), $delta, $message);

        return $this;
    }
    // example-end: assertEqualsWithDelta

    // example-start: assertNotEqualsCanonicalizing
    /**
     * Asserts that two variables are not equal (canonicalizing).
     *
     * @param mixed $expected
     */
    public function assertNotEqualsCanonicalizing($expected, string $message = ''): self
    {
        Assert::assertNotEqualsCanonicalizing($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotEqualsCanonicalizing

    // example-start: assertNotEqualsIgnoringCase
    /**
     * Asserts that two variables are not equal (ignoring case).
     *
     * @param mixed $expected
     */
    public function assertNotEqualsIgnoringCase($expected, string $message = ''): self
    {
        Assert::assertNotEqualsIgnoringCase($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertNotEqualsIgnoringCase

    // example-start: assertNotEqualsWithDelta
    /**
     * Asserts that two variables are not equal (with delta).
     *
     * @param mixed $expected
     */
    public function assertNotEqualsWithDelta($expected, float $delta, string $message = ''): self
    {
        Assert::assertNotEqualsWithDelta($expected, $this->getData(), $delta, $message);

        return $this;
    }
    // example-end: assertNotEqualsWithDelta

    // example-start: assertIsArray
    /**
     * Asserts that a variable is of type array.
     */
    public function assertIsArray(string $message = ''): self
    {
        Assert::assertIsArray($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsArray

    // example-start: assertIsBool
    /**
     * Asserts that a variable is of type bool.
     */
    public function assertIsBool(string $message = ''): self
    {
        Assert::assertIsBool($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsBool

    // example-start: assertIsFloat
    /**
     * Asserts that a variable is of type float.
     */
    public function assertIsFloat(string $message = ''): self
    {
        Assert::assertIsFloat($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsFloat

    // example-start: assertIsInt
    /**
     * Asserts that a variable is of type int.
     */
    public function assertIsInt(string $message = ''): self
    {
        Assert::assertIsInt($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsInt

    // example-start: assertIsNumeric
    /**
     * Asserts that a variable is of type numeric.
     */
    public function assertIsNumeric(string $message = ''): self
    {
        Assert::assertIsNumeric($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNumeric

    // example-start: assertIsObject
    /**
     * Asserts that a variable is of type object.
     */
    public function assertIsObject(string $message = ''): self
    {
        Assert::assertIsObject($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsObject

    // example-start: assertIsResource
    /**
     * Asserts that a variable is of type resource.
     */
    public function assertIsResource(string $message = ''): self
    {
        Assert::assertIsResource($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsResource

    // example-start: assertIsString
    /**
     * Asserts that a variable is of type string.
     */
    public function assertIsString(string $message = ''): self
    {
        Assert::assertIsString($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsString

    // example-start: assertIsScalar
    /**
     * Asserts that a variable is of type scalar.
     */
    public function assertIsScalar(string $message = ''): self
    {
        Assert::assertIsScalar($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsScalar

    // example-start: assertIsCallable
    /**
     * Asserts that a variable is of type callable.
     */
    public function assertIsCallable(string $message = ''): self
    {
        Assert::assertIsCallable($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsCallable

    // example-start: assertIsIterable
    /**
     * Asserts that a variable is of type iterable.
     */
    public function assertIsIterable(string $message = ''): self
    {
        Assert::assertIsIterable($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsIterable

    // example-start: assertIsNotArray
    /**
     * Asserts that a variable is not of type array.
     */
    public function assertIsNotArray(string $message = ''): self
    {
        Assert::assertIsNotArray($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotArray

    // example-start: assertIsNotBool
    /**
     * Asserts that a variable is not of type bool.
     */
    public function assertIsNotBool(string $message = ''): self
    {
        Assert::assertIsNotBool($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotBool

    // example-start: assertIsNotFloat
    /**
     * Asserts that a variable is not of type float.
     */
    public function assertIsNotFloat(string $message = ''): self
    {
        Assert::assertIsNotFloat($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotFloat

    // example-start: assertIsNotInt
    /**
     * Asserts that a variable is not of type int.
     */
    public function assertIsNotInt(string $message = ''): self
    {
        Assert::assertIsNotInt($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotInt

    // example-start: assertIsNotNumeric
    /**
     * Asserts that a variable is not of type numeric.
     */
    public function assertIsNotNumeric(string $message = ''): self
    {
        Assert::assertIsNotNumeric($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotNumeric

    // example-start: assertIsNotObject
    /**
     * Asserts that a variable is not of type object.
     */
    public function assertIsNotObject(string $message = ''): self
    {
        Assert::assertIsNotObject($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotObject

    // example-start: assertIsNotResource
    /**
     * Asserts that a variable is not of type resource.
     */
    public function assertIsNotResource(string $message = ''): self
    {
        Assert::assertIsNotResource($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotResource

    // example-start: assertIsNotString
    /**
     * Asserts that a variable is not of type string.
     */
    public function assertIsNotString(string $message = ''): self
    {
        Assert::assertIsNotString($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotString

    // example-start: assertIsNotScalar
    /**
     * Asserts that a variable is not of type scalar.
     */
    public function assertIsNotScalar(string $message = ''): self
    {
        Assert::assertIsNotScalar($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotScalar

    // example-start: assertIsNotCallable
    /**
     * Asserts that a variable is not of type callable.
     */
    public function assertIsNotCallable(string $message = ''): self
    {
        Assert::assertIsNotCallable($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotCallable

    // example-start: assertIsNotIterable
    /**
     * Asserts that a variable is not of type iterable.
     */
    public function assertIsNotIterable(string $message = ''): self
    {
        Assert::assertIsNotIterable($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotIterable

    // example-start: assertStringContainsString

    public function assertStringContainsString(string $needle, string $message = ''): self
    {
        Assert::assertStringContainsString($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringContainsString

    // example-start: assertStringContainsStringIgnoringCase

    public function assertStringContainsStringIgnoringCase(string $needle, string $message = ''): self
    {
        Assert::assertStringContainsStringIgnoringCase($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringContainsStringIgnoringCase

    // example-start: assertStringNotContainsString

    public function assertStringNotContainsString(string $needle, string $message = ''): self
    {
        Assert::assertStringNotContainsString($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringNotContainsString

    // example-start: assertStringNotContainsStringIgnoringCase

    public function assertStringNotContainsStringIgnoringCase(string $needle, string $message = ''): self
    {
        Assert::assertStringNotContainsStringIgnoringCase($needle, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringNotContainsStringIgnoringCase

    // example-start: assertObjectEquals

    public function assertObjectEquals(object $expected, string $method = 'equals', string $message = ''): self
    {
        Assert::assertObjectEquals($expected, $this->getData(), $method, $message);

        return $this;
    }
    // example-end: assertObjectEquals

    // example-start: assertFileEqualsCanonicalizing
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (canonicalizing).
     */
    public function assertFileEqualsCanonicalizing(string $expected, string $message = ''): self
    {
        Assert::assertFileEqualsCanonicalizing($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertFileEqualsCanonicalizing

    // example-start: assertFileEqualsIgnoringCase
    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (ignoring case).
     */
    public function assertFileEqualsIgnoringCase(string $expected, string $message = ''): self
    {
        Assert::assertFileEqualsIgnoringCase($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertFileEqualsIgnoringCase

    // example-start: assertFileNotEqualsCanonicalizing
    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (canonicalizing).
     */
    public function assertFileNotEqualsCanonicalizing(string $expected, string $message = ''): self
    {
        Assert::assertFileNotEqualsCanonicalizing($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertFileNotEqualsCanonicalizing

    // example-start: assertFileNotEqualsIgnoringCase
    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (ignoring case).
     */
    public function assertFileNotEqualsIgnoringCase(string $expected, string $message = ''): self
    {
        Assert::assertFileNotEqualsIgnoringCase($expected, $this->getData(), $message);

        return $this;
    }
    // example-end: assertFileNotEqualsIgnoringCase

    // example-start: assertStringEqualsFileCanonicalizing
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (canonicalizing).
     */
    public function assertStringEqualsFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFileCanonicalizing($expectedFile, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringEqualsFileCanonicalizing

    // example-start: assertStringEqualsFileIgnoringCase
    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (ignoring case).
     */
    public function assertStringEqualsFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringEqualsFileIgnoringCase($expectedFile, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringEqualsFileIgnoringCase

    // example-start: assertStringNotEqualsFileCanonicalizing
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (canonicalizing).
     */
    public function assertStringNotEqualsFileCanonicalizing(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFileCanonicalizing($expectedFile, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringNotEqualsFileCanonicalizing

    // example-start: assertStringNotEqualsFileIgnoringCase
    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (ignoring case).
     */
    public function assertStringNotEqualsFileIgnoringCase(string $expectedFile, string $message = ''): self
    {
        Assert::assertStringNotEqualsFileIgnoringCase($expectedFile, $this->getData(), $message);

        return $this;
    }
    // example-end: assertStringNotEqualsFileIgnoringCase

    // example-start: assertIsNotReadable
    /**
     * Asserts that a file/dir exists and is not readable.
     */
    public function assertIsNotReadable(string $message = ''): self
    {
        Assert::assertIsNotReadable($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotReadable

    // example-start: assertIsNotWritable
    /**
     * Asserts that a file/dir exists and is not writable.
     */
    public function assertIsNotWritable(string $message = ''): self
    {
        Assert::assertIsNotWritable($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotWritable

    // example-start: assertDirectoryDoesNotExist
    /**
     * Asserts that a directory does not exist.
     */
    public function assertDirectoryDoesNotExist(string $message = ''): self
    {
        Assert::assertDirectoryDoesNotExist($this->getData(), $message);

        return $this;
    }
    // example-end: assertDirectoryDoesNotExist

    // example-start: assertDirectoryIsNotReadable
    /**
     * Asserts that a directory exists and is not readable.
     */
    public function assertDirectoryIsNotReadable(string $message = ''): self
    {
        Assert::assertDirectoryIsNotReadable($this->getData(), $message);

        return $this;
    }
    // example-end: assertDirectoryIsNotReadable

    // example-start: assertDirectoryIsNotWritable
    /**
     * Asserts that a directory exists and is not writable.
     */
    public function assertDirectoryIsNotWritable(string $message = ''): self
    {
        Assert::assertDirectoryIsNotWritable($this->getData(), $message);

        return $this;
    }
    // example-end: assertDirectoryIsNotWritable

    // example-start: assertFileDoesNotExist
    /**
     * Asserts that a file does not exist.
     */
    public function assertFileDoesNotExist(string $message = ''): self
    {
        Assert::assertFileDoesNotExist($this->getData(), $message);

        return $this;
    }
    // example-end: assertFileDoesNotExist

    // example-start: assertFileIsNotReadable
    /**
     * Asserts that a file exists and is not readable.
     */
    public function assertFileIsNotReadable(string $message = ''): self
    {
        Assert::assertFileIsNotReadable($this->getData(), $message);

        return $this;
    }
    // example-end: assertFileIsNotReadable

    // example-start: assertFileIsNotWritable
    /**
     * Asserts that a file exists and is not writable.
     */
    public function assertFileIsNotWritable(string $message = ''): self
    {
        Assert::assertFileIsNotWritable($this->getData(), $message);

        return $this;
    }
    // example-end: assertFileIsNotWritable

    // example-start: assertIsClosedResource
    /**
     * Asserts that a variable is of type resource and is closed.
     */
    public function assertIsClosedResource(string $message = ''): self
    {
        Assert::assertIsClosedResource($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsClosedResource

    // example-start: assertIsNotClosedResource
    /**
     * Asserts that a variable is not of type resource.
     */
    public function assertIsNotClosedResource(string $message = ''): self
    {
        Assert::assertIsNotClosedResource($this->getData(), $message);

        return $this;
    }
    // example-end: assertIsNotClosedResource

    // example-start: assertMatchesRegularExpression
    /**
     * Asserts that a string matches a given regular expression.
     */
    public function assertMatchesRegularExpression(string $pattern, string $message = ''): self
    {
        Assert::assertMatchesRegularExpression($pattern, $this->getData(), $message);

        return $this;
    }
    // example-end: assertMatchesRegularExpression

    // example-start: assertDoesNotMatchRegularExpression
    /**
     * Asserts that a string does not match a given regular expression.
     */
    public function assertDoesNotMatchRegularExpression(string $pattern, string $message = ''): self
    {
        Assert::assertDoesNotMatchRegularExpression($pattern, $this->getData(), $message);

        return $this;
    }
    // example-end: assertDoesNotMatchRegularExpression
}
