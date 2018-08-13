<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-trait.php script.
 * Do not modify manually.
 */

namespace Draw\DataTester;

use PHPUnit\Framework\Assert;
use ArrayAccess;
use Countable;
use Traversable;

trait AssertTrait
{
    /**
     * @return mixed Return the data that is currently tested
     */
    abstract public function getData();

    //example-start: assertArraySubset    

    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess $subset
     * @param bool $strict Check for object identity
     * @param string $message
     * @return $this
     */
    public function assertArraySubset($subset, $strict = false, $message = '')
    {
        Assert::assertArraySubset($subset, $this->getData(), $strict, $message);

        return $this;
    }
    //example-end: assertArraySubset  

    //example-start: assertContains    
    /**
     * Asserts that a haystack contains a needle.
     *
     * @param mixed $needle
     * @param string $message
     * @param bool $ignoreCase
     * @param bool $checkForObjectIdentity
     * @param bool $checkForNonObjectIdentity
     * @return $this
     */
    public function assertContains(
        $needle,
        $message = '',
        $ignoreCase = false,
        $checkForObjectIdentity = true,
        $checkForNonObjectIdentity = false
    ) {
        Assert::assertContains($needle, $this->getData(), $message, $ignoreCase, $checkForObjectIdentity,
            $checkForNonObjectIdentity);

        return $this;
    }
    //example-end: assertContains  

    //example-start: assertNotContains    
    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param mixed $needle
     * @param string $message
     * @param bool $ignoreCase
     * @param bool $checkForObjectIdentity
     * @param bool $checkForNonObjectIdentity
     * @return $this
     */
    public function assertNotContains(
        $needle,
        $message = '',
        $ignoreCase = false,
        $checkForObjectIdentity = true,
        $checkForNonObjectIdentity = false
    ) {
        Assert::assertNotContains($needle, $this->getData(), $message, $ignoreCase, $checkForObjectIdentity,
            $checkForNonObjectIdentity);

        return $this;
    }
    //example-end: assertNotContains  

    //example-start: assertContainsOnly    
    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @param string $type
     * @param bool $isNativeType
     * @param string $message
     * @return $this
     */
    public function assertContainsOnly($type, $isNativeType = null, $message = '')
    {
        Assert::assertContainsOnly($type, $this->getData(), $isNativeType, $message);

        return $this;
    }
    //example-end: assertContainsOnly  

    //example-start: assertContainsOnlyInstancesOf    
    /**
     * Asserts that a haystack contains only instances of a given classname
     *
     * @param string $classname
     * @param string $message
     * @return $this
     */
    public function assertContainsOnlyInstancesOf($classname, $message = '')
    {
        Assert::assertContainsOnlyInstancesOf($classname, $this->getData(), $message);

        return $this;
    }
    //example-end: assertContainsOnlyInstancesOf  

    //example-start: assertNotContainsOnly    
    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @param string $type
     * @param bool $isNativeType
     * @param string $message
     * @return $this
     */
    public function assertNotContainsOnly($type, $isNativeType = null, $message = '')
    {
        Assert::assertNotContainsOnly($type, $this->getData(), $isNativeType, $message);

        return $this;
    }
    //example-end: assertNotContainsOnly  

    //example-start: assertCount    
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int $expectedCount
     * @param string $message
     * @return $this
     */
    public function assertCount($expectedCount, $message = '')
    {
        Assert::assertCount($expectedCount, $this->getData(), $message);

        return $this;
    }
    //example-end: assertCount  

    //example-start: assertNotCount    
    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int $expectedCount
     * @param string $message
     * @return $this
     */
    public function assertNotCount($expectedCount, $message = '')
    {
        Assert::assertNotCount($expectedCount, $this->getData(), $message);

        return $this;
    }
    //example-end: assertNotCount  

    //example-start: assertEquals    
    /**
     * Asserts that two variables are equal.
     *
     * @param mixed $expected
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @param bool $canonicalize
     * @param bool $ignoreCase
     * @return $this
     */
    public function assertEquals(
        $expected,
        $message = '',
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        Assert::assertEquals($expected, $this->getData(), $message, $delta, $maxDepth, $canonicalize, $ignoreCase);

        return $this;
    }
    //example-end: assertEquals  

    //example-start: assertNotEquals    
    /**
     * Asserts that two variables are not equal.
     *
     * @param mixed $expected
     * @param string $message
     * @param float $delta
     * @param int $maxDepth
     * @param bool $canonicalize
     * @param bool $ignoreCase
     * @return $this
     */
    public function assertNotEquals(
        $expected,
        $message = '',
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        Assert::assertNotEquals($expected, $this->getData(), $message, $delta, $maxDepth, $canonicalize, $ignoreCase);

        return $this;
    }
    //example-end: assertNotEquals  

    //example-start: assertEmpty    
    /**
     * Asserts that a variable is empty.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertEmpty($message = '')
    {
        Assert::assertEmpty($this->getData(), $message);

        return $this;
    }
    //example-end: assertEmpty  

    //example-start: assertNotEmpty    
    /**
     * Asserts that a variable is not empty.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertNotEmpty($message = '')
    {
        Assert::assertNotEmpty($this->getData(), $message);

        return $this;
    }
    //example-end: assertNotEmpty  

    //example-start: assertGreaterThan    
    /**
     * Asserts that a value is greater than another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertGreaterThan($expected, $message = '')
    {
        Assert::assertGreaterThan($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertGreaterThan  

    //example-start: assertGreaterThanOrEqual    
    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertGreaterThanOrEqual($expected, $message = '')
    {
        Assert::assertGreaterThanOrEqual($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertGreaterThanOrEqual  

    //example-start: assertLessThan    
    /**
     * Asserts that a value is smaller than another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertLessThan($expected, $message = '')
    {
        Assert::assertLessThan($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertLessThan  

    //example-start: assertLessThanOrEqual    
    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertLessThanOrEqual($expected, $message = '')
    {
        Assert::assertLessThanOrEqual($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertLessThanOrEqual  

    //example-start: assertTrue    
    /**
     * Asserts that a condition is true.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertTrue($message = '')
    {
        Assert::assertTrue($this->getData(), $message);

        return $this;
    }
    //example-end: assertTrue  

    //example-start: assertNotTrue    
    /**
     * Asserts that a condition is not true.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertNotTrue($message = '')
    {
        Assert::assertNotTrue($this->getData(), $message);

        return $this;
    }
    //example-end: assertNotTrue  

    //example-start: assertFalse    
    /**
     * Asserts that a condition is false.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertFalse($message = '')
    {
        Assert::assertFalse($this->getData(), $message);

        return $this;
    }
    //example-end: assertFalse  

    //example-start: assertNotFalse    
    /**
     * Asserts that a condition is not false.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertNotFalse($message = '')
    {
        Assert::assertNotFalse($this->getData(), $message);

        return $this;
    }
    //example-end: assertNotFalse  

    //example-start: assertNull    
    /**
     * Asserts that a variable is null.
     *
     * @param string $message
     * @return $this
     */
    public function assertNull($message = '')
    {
        Assert::assertNull($this->getData(), $message);

        return $this;
    }
    //example-end: assertNull  

    //example-start: assertNotNull    
    /**
     * Asserts that a variable is not null.
     *
     * @param string $message
     * @return $this
     */
    public function assertNotNull($message = '')
    {
        Assert::assertNotNull($this->getData(), $message);

        return $this;
    }
    //example-end: assertNotNull  

    //example-start: assertFinite    
    /**
     * Asserts that a variable is finite.
     *
     * @param string $message
     * @return $this
     */
    public function assertFinite($message = '')
    {
        Assert::assertFinite($this->getData(), $message);

        return $this;
    }
    //example-end: assertFinite  

    //example-start: assertInfinite    
    /**
     * Asserts that a variable is infinite.
     *
     * @param string $message
     * @return $this
     */
    public function assertInfinite($message = '')
    {
        Assert::assertInfinite($this->getData(), $message);

        return $this;
    }
    //example-end: assertInfinite  

    //example-start: assertNan    
    /**
     * Asserts that a variable is nan.
     *
     * @param string $message
     * @return $this
     */
    public function assertNan($message = '')
    {
        Assert::assertNan($this->getData(), $message);

        return $this;
    }
    //example-end: assertNan  

    //example-start: assertSame    
    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertSame($expected, $message = '')
    {
        Assert::assertSame($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertSame  

    //example-start: assertNotSame    
    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertNotSame($expected, $message = '')
    {
        Assert::assertNotSame($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertNotSame  

    //example-start: assertInstanceOf    
    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertInstanceOf($expected, $message = '')
    {
        Assert::assertInstanceOf($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertInstanceOf  

    //example-start: assertNotInstanceOf    
    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertNotInstanceOf($expected, $message = '')
    {
        Assert::assertNotInstanceOf($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertNotInstanceOf  

    //example-start: assertInternalType    
    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertInternalType($expected, $message = '')
    {
        Assert::assertInternalType($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertInternalType  

    //example-start: assertNotInternalType    
    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertNotInternalType($expected, $message = '')
    {
        Assert::assertNotInternalType($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertNotInternalType  

    //example-start: assertRegExp    
    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return $this
     */
    public function assertRegExp($pattern, $message = '')
    {
        Assert::assertRegExp($pattern, $this->getData(), $message);

        return $this;
    }
    //example-end: assertRegExp  

    //example-start: assertNotRegExp    
    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return $this
     */
    public function assertNotRegExp($pattern, $message = '')
    {
        Assert::assertNotRegExp($pattern, $this->getData(), $message);

        return $this;
    }
    //example-end: assertNotRegExp  

    //example-start: assertSameSize    
    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param array|Countable|Traversable $expected
     * @param string $message
     * @return $this
     */
    public function assertSameSize($expected, $message = '')
    {
        Assert::assertSameSize($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertSameSize  

    //example-start: assertNotSameSize    
    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is not the same.
     *
     * @param array|Countable|Traversable $expected
     * @param string $message
     * @return $this
     */
    public function assertNotSameSize($expected, $message = '')
    {
        Assert::assertNotSameSize($expected, $this->getData(), $message);

        return $this;
    }
    //example-end: assertNotSameSize  

    //example-start: assertStringMatchesFormat    
    /**
     * Asserts that a string matches a given format string.
     *
     * @param string $format
     * @param string $message
     * @return $this
     */
    public function assertStringMatchesFormat($format, $message = '')
    {
        Assert::assertStringMatchesFormat($format, $this->getData(), $message);

        return $this;
    }
    //example-end: assertStringMatchesFormat  

    //example-start: assertStringNotMatchesFormat    
    /**
     * Asserts that a string does not match a given format string.
     *
     * @param string $format
     * @param string $message
     * @return $this
     */
    public function assertStringNotMatchesFormat($format, $message = '')
    {
        Assert::assertStringNotMatchesFormat($format, $this->getData(), $message);

        return $this;
    }
    //example-end: assertStringNotMatchesFormat  

    //example-start: assertStringStartsWith    
    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return $this
     */
    public function assertStringStartsWith($prefix, $message = '')
    {
        Assert::assertStringStartsWith($prefix, $this->getData(), $message);

        return $this;
    }
    //example-end: assertStringStartsWith  

    //example-start: assertStringStartsNotWith    
    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return $this
     */
    public function assertStringStartsNotWith($prefix, $message = '')
    {
        Assert::assertStringStartsNotWith($prefix, $this->getData(), $message);

        return $this;
    }
    //example-end: assertStringStartsNotWith  

    //example-start: assertStringEndsWith    
    /**
     * Asserts that a string ends with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return $this
     */
    public function assertStringEndsWith($suffix, $message = '')
    {
        Assert::assertStringEndsWith($suffix, $this->getData(), $message);

        return $this;
    }
    //example-end: assertStringEndsWith  

    //example-start: assertStringEndsNotWith    
    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return $this
     */
    public function assertStringEndsNotWith($suffix, $message = '')
    {
        Assert::assertStringEndsNotWith($suffix, $this->getData(), $message);

        return $this;
    }
    //example-end: assertStringEndsNotWith  

    //example-start: assertJson    
    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param string $message
     * @return $this
     */
    public function assertJson($message = '')
    {
        Assert::assertJson($this->getData(), $message);

        return $this;
    }
    //example-end: assertJson  

    //example-start: assertJsonStringEqualsJsonString    
    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return $this
     */
    public function assertJsonStringEqualsJsonString($expectedJson, $message = '')
    {
        Assert::assertJsonStringEqualsJsonString($expectedJson, $this->getData(), $message);

        return $this;
    }
    //example-end: assertJsonStringEqualsJsonString  

    //example-start: assertJsonStringNotEqualsJsonString    
    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return $this
     */
    public function assertJsonStringNotEqualsJsonString($expectedJson, $message = '')
    {
        Assert::assertJsonStringNotEqualsJsonString($expectedJson, $this->getData(), $message);

        return $this;
    }
    //example-end: assertJsonStringNotEqualsJsonString  

}