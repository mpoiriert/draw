<?php

namespace Draw\Component\Tester\Tests;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class DataTesterTest extends TestCase
{
    public function testAssertPathIsNotReadable(): void
    {
        $tester = new DataTester(null);

        $this->assertSame(
            $tester,
            $tester->assertPathIsNotReadable('toto')
        );
    }

    public function testAssertPathIsReadable(): void
    {
        $tester = new DataTester((object) ['key' => 'value']);

        $this->assertSame(
            $tester,
            $tester->assertPathIsReadable('key')
        );
    }

    public function testPath(): void
    {
        $tester = new DataTester((object) ['key' => 'value']);

        $this->assertNotSame(
            $tester,
            $newTester = $tester->path('key'),
            'Return value of path must be a new object.'
        );

        $this->assertInstanceOf(DataTester::class, $tester);
        $this->assertSame('value', $newTester->getData());
    }

    /**
     * @depends testPath
     */
    public function testChain(): void
    {
        $data = new \stdClass();
        $data->key1 = 'value1';
        $data->key2 = ['arrayValue0', 'arrayValue1'];

        $tester = new DataTester($data);
        $tester->assertPathIsNotReadable('[key1]', 'The data is a object, should not be read like a array.');

        $tester->path('key1')->assertSame('value1');
        $tester
            ->path('key2')->assertCount(2)
            ->path('[0]')->assertSame('arrayValue0');

        $tester->path('key2[1]')->assertSame('arrayValue1');
    }

    public function testIfPathIsReadable(): void
    {
        $hasBeenCalled = false;

        $tester = new DataTester(null);

        $this->assertSame(
            $tester,
            $tester->ifPathIsReadable(
                'toto',
                function (DataTester $tester) use (&$hasBeenCalled) {
                    // To remove the warning of the ide we use the variable
                    // assigning true would have been enough
                    $hasBeenCalled = null !== $tester;
                }
            )
        );

        $this->assertFalse($hasBeenCalled, 'The path is not readable the callable should not have been called.');
    }

    /**
     * @depends testIfPathIsReadable
     * @depends testPath
     */
    public function testEach(): void
    {
        $users = [
            ['firstName' => 'Martin', 'active' => true, 'referral' => 'Google'],
            ['firstName' => 'Julie', 'active' => false],
        ];

        $callbackCount = 0;

        $tester = new DataTester($users);

        $this->assertSame(
            $tester,
            $tester->each(
                function (DataTester $tester) use (&$callbackCount) {
                    ++$callbackCount;
                }
            )
        );

        $this->assertSame(count($users), $callbackCount);
    }

    /**
     * @depends testPath
     */
    public function testTransform(): void
    {
        $tester = new DataTester('{"key":"value"}');

        $this->assertNotSame(
            $tester,
            $newTester = $tester->assertJson()->transform('json_decode')
        );

        $this->assertInstanceOf(DataTester::class, $newTester);
        $newTester->path('key')->assertSame('value');
    }

    public function testCreateCallable(): void
    {
        (new DataTester(1))->test(DataTester::createCallable('assertIsInt'));
    }

    public function testCreateCallableFailing(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('This is not a string.');

        (new DataTester(1))->test(DataTester::createCallable('assertIsString', 'This is not a string.'));
    }
}
