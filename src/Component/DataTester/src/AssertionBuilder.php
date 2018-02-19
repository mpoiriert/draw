<?php
/**
 * This file is auto generated via the draw/php-data-tester/bin/generate-assertion-builder.php script.
 * Do not modify manually.
 */

namespace Draw\DataTester;

use ArrayAccess;
use Countable;
use Traversable;

class AssertionBuilder
{
    private $assertions = [];

    public function __invoke(Tester $tester)
    {
        foreach ($this->assertions as $assertion) {
            $methodName = array_shift($assertion);
            call_user_func_array([$tester, $methodName], $assertion);
        }
    }

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
        $this->assertions[] = array_merge(['assertArraySubset'], func_get_args());

        return $this;
    }

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
    public function assertContains($needle, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        $this->assertions[] = array_merge(['assertContains'], func_get_args());

        return $this;
    }

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
    public function assertNotContains($needle, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        $this->assertions[] = array_merge(['assertNotContains'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @param string $type
     * @param bool $isNativeType
     * @param string $message
     * @return $this
     */
    public function assertContainsOnly($type, $isNativeType = NULL, $message = '')
    {
        $this->assertions[] = array_merge(['assertContainsOnly'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a haystack contains only instances of a given classname
     *
     * @param string $classname
     * @param string $message
     * @return $this
     */
    public function assertContainsOnlyInstancesOf($classname, $message = '')
    {
        $this->assertions[] = array_merge(['assertContainsOnlyInstancesOf'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @param string $type
     * @param bool $isNativeType
     * @param string $message
     * @return $this
     */
    public function assertNotContainsOnly($type, $isNativeType = NULL, $message = '')
    {
        $this->assertions[] = array_merge(['assertNotContainsOnly'], func_get_args());

        return $this;
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int $expectedCount
     * @param string $message
     * @return $this
     */
    public function assertCount($expectedCount, $message = '')
    {
        $this->assertions[] = array_merge(['assertCount'], func_get_args());

        return $this;
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int $expectedCount
     * @param string $message
     * @return $this
     */
    public function assertNotCount($expectedCount, $message = '')
    {
        $this->assertions[] = array_merge(['assertNotCount'], func_get_args());

        return $this;
    }

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
    public function assertEquals($expected, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertions[] = array_merge(['assertEquals'], func_get_args());

        return $this;
    }

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
    public function assertNotEquals($expected, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertions[] = array_merge(['assertNotEquals'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is empty.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertEmpty($message = '')
    {
        $this->assertions[] = array_merge(['assertEmpty'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is not empty.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertNotEmpty($message = '')
    {
        $this->assertions[] = array_merge(['assertNotEmpty'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a value is greater than another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertGreaterThan($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertGreaterThan'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertGreaterThanOrEqual($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertGreaterThanOrEqual'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a value is smaller than another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertLessThan($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertLessThan'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @param mixed $expected
     * @param string $message
     * @return $this
     */
    public function assertLessThanOrEqual($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertLessThanOrEqual'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a condition is true.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertTrue($message = '')
    {
        $this->assertions[] = array_merge(['assertTrue'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a condition is not true.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertNotTrue($message = '')
    {
        $this->assertions[] = array_merge(['assertNotTrue'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a condition is false.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertFalse($message = '')
    {
        $this->assertions[] = array_merge(['assertFalse'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a condition is not false.
     *
     * @param string $message
     *
     * @return $this
     */
    public function assertNotFalse($message = '')
    {
        $this->assertions[] = array_merge(['assertNotFalse'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is null.
     *
     * @param string $message
     * @return $this
     */
    public function assertNull($message = '')
    {
        $this->assertions[] = array_merge(['assertNull'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is not null.
     *
     * @param string $message
     * @return $this
     */
    public function assertNotNull($message = '')
    {
        $this->assertions[] = array_merge(['assertNotNull'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is finite.
     *
     * @param string $message
     * @return $this
     */
    public function assertFinite($message = '')
    {
        $this->assertions[] = array_merge(['assertFinite'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is infinite.
     *
     * @param string $message
     * @return $this
     */
    public function assertInfinite($message = '')
    {
        $this->assertions[] = array_merge(['assertInfinite'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is nan.
     *
     * @param string $message
     * @return $this
     */
    public function assertNan($message = '')
    {
        $this->assertions[] = array_merge(['assertNan'], func_get_args());

        return $this;
    }

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
        $this->assertions[] = array_merge(['assertSame'], func_get_args());

        return $this;
    }

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
        $this->assertions[] = array_merge(['assertNotSame'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertInstanceOf($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertInstanceOf'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertNotInstanceOf($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertNotInstanceOf'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertInternalType($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertInternalType'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param string $message
     * @return $this
     */
    public function assertNotInternalType($expected, $message = '')
    {
        $this->assertions[] = array_merge(['assertNotInternalType'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return $this
     */
    public function assertRegExp($pattern, $message = '')
    {
        $this->assertions[] = array_merge(['assertRegExp'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @param string $pattern
     * @param string $message
     * @return $this
     */
    public function assertNotRegExp($pattern, $message = '')
    {
        $this->assertions[] = array_merge(['assertNotRegExp'], func_get_args());

        return $this;
    }

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
        $this->assertions[] = array_merge(['assertSameSize'], func_get_args());

        return $this;
    }

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
        $this->assertions[] = array_merge(['assertNotSameSize'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string matches a given format string.
     *
     * @param string $format
     * @param string $message
     * @return $this
     */
    public function assertStringMatchesFormat($format, $message = '')
    {
        $this->assertions[] = array_merge(['assertStringMatchesFormat'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @param string $format
     * @param string $message
     * @return $this
     */
    public function assertStringNotMatchesFormat($format, $message = '')
    {
        $this->assertions[] = array_merge(['assertStringNotMatchesFormat'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param string $prefix
     * @param string $message
     * @return $this
     */
    public function assertStringStartsNotWith($prefix, $message = '')
    {
        $this->assertions[] = array_merge(['assertStringStartsNotWith'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string ends with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return $this
     */
    public function assertStringEndsWith($suffix, $message = '')
    {
        $this->assertions[] = array_merge(['assertStringEndsWith'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @param string $suffix
     * @param string $message
     * @return $this
     */
    public function assertStringEndsNotWith($suffix, $message = '')
    {
        $this->assertions[] = array_merge(['assertStringEndsNotWith'], func_get_args());

        return $this;
    }

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param string $message
     * @return $this
     */
    public function assertJson($message = '')
    {
        $this->assertions[] = array_merge(['assertJson'], func_get_args());

        return $this;
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return $this
     */
    public function assertJsonStringEqualsJsonString($expectedJson, $message = '')
    {
        $this->assertions[] = array_merge(['assertJsonStringEqualsJsonString'], func_get_args());

        return $this;
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $message
     * @return $this
     */
    public function assertJsonStringNotEqualsJsonString($expectedJson, $message = '')
    {
        $this->assertions[] = array_merge(['assertJsonStringNotEqualsJsonString'], func_get_args());

        return $this;
    }

}