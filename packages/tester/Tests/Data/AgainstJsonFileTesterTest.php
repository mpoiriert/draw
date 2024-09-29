<?php

namespace Draw\Component\Tester\Tests\Data;

use Draw\Component\Tester\Data\AgainstJsonFileTester;
use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class AgainstJsonFileTesterTest extends TestCase
{
    private static \stdClass $data;

    public static function setUpBeforeClass(): void
    {
        self::$data = (object) ['url' => 'http://google.com'];
    }

    public function testInvoke(): void
    {
        (new DataTester(self::$data))
            ->test(new AgainstJsonFileTester(__DIR__.'/fixtures/AgainstJsonFileTesterTest.json'));
    }

    public function testInvokeDoesNotMatch(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        (new DataTester((object) ['url' => 'toto']))
            ->test(new AgainstJsonFileTester(__DIR__.'/fixtures/AgainstJsonFileTesterTest.json'));
    }

    public function testInvokePropertyPathChecksValue(): void
    {
        (new DataTester(self::$data))
            ->test(
                new AgainstJsonFileTester(
                    __DIR__.'/fixtures/AgainstJsonFileTesterTest_testInvoke_propertyPathChecks_equal.json',
                    [
                        'url' => 'http://google.com',
                    ]
                )
            );
    }

    public function testInvokePropertyPathChecksViaConstraint(): void
    {
        (new DataTester(self::$data))
            ->test(
                new AgainstJsonFileTester(
                    __DIR__.'/fixtures/AgainstJsonFileTesterTest_testInvoke_propertyPathChecks_equal.json',
                    [
                        'url' => new IsType(IsType::TYPE_STRING),
                    ]
                )
            );
    }

    public function testInvokePropertyPathChecksValueFail(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Path: url
Failed asserting that two strings are equal.');

        (new DataTester(self::$data))
            ->test(
                new AgainstJsonFileTester(
                    __DIR__.'/fixtures/AgainstJsonFileTesterTest.json',
                    [
                        'url' => 'wrong-value',
                    ]
                )
            );
    }

    public function testInvokePropertyPathChecksCallable(): void
    {
        $called = false;
        (new DataTester(self::$data))
            ->test(
                new AgainstJsonFileTester(
                    __DIR__.'/fixtures/AgainstJsonFileTesterTest.json',
                    [
                        'url' => function (DataTester $dataTester) use (&$called): void {
                            // Make sure the DataTester have the value of the path
                            $dataTester->assertEquals('http://google.com');

                            $called = true;
                        },
                    ]
                )
            );
        static::assertTrue($called);
    }

    public function testInvokePropertyPathChecksCallableFail(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that two strings are equal.');
        (new DataTester(self::$data))
            ->test(
                new AgainstJsonFileTester(
                    __DIR__.'/fixtures/AgainstJsonFileTesterTest.json',
                    [
                        'url' => function (DataTester $dataTester): void {
                            // Make sure the DataTester have the value of the path
                            $dataTester->assertEquals('wrong-value');
                        },
                    ]
                )
            );
    }

    public function testInvokeFileNotFoundException(): void
    {
        $filePath = __DIR__.'/does-not-exists.json';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Fail path does not exists to validate data.
File path: %s
Data:
{
    "url": "http://google.com"
}',
                $filePath
            )
        );

        (new DataTester(self::$data))
            ->test(new AgainstJsonFileTester($filePath));
    }
}
