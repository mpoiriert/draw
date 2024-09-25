<?php

namespace Draw\Component\Tester\Tests;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;

class DataTesterTest extends TestCase
{
    public function testAssertPathIsNotReadable(): void
    {
        $tester = new DataTester(null);

        static::assertSame(
            $tester,
            $tester->assertPathIsNotReadable('toto')
        );
    }

    public function testAssertPathIsReadable(): void
    {
        $tester = new DataTester((object) ['key' => 'value']);

        static::assertSame(
            $tester,
            $tester->assertPathIsReadable('key')
        );
    }

    public function testPath(): void
    {
        $tester = new DataTester((object) ['key' => 'value']);

        static::assertNotSame(
            $tester,
            $newTester = $tester->path('key'),
            'Return value of path must be a new object.'
        );

        static::assertInstanceOf(DataTester::class, $tester);
        static::assertSame('value', $newTester->getData());
    }

    #[Depends('testPath')]
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

        static::assertSame(
            $tester,
            $tester->ifPathIsReadable(
                'toto',
                function (DataTester $tester) use (&$hasBeenCalled): void {
                    // To remove the warning of the ide we use the variable
                    // assigning true would have been enough
                    $hasBeenCalled = null !== $tester;
                }
            )
        );

        static::assertFalse($hasBeenCalled, 'The path is not readable the callable should not have been called.');
    }

    #[
        Depends('testIfPathIsReadable'),
        Depends('testPath'),
    ]
    public function testEach(): void
    {
        $users = [
            ['firstName' => 'Martin', 'active' => true, 'referral' => 'Google'],
            ['firstName' => 'Julie', 'active' => false],
        ];

        $callbackCount = 0;

        $tester = new DataTester($users);

        static::assertSame(
            $tester,
            $tester->each(
                function (DataTester $tester) use (&$callbackCount): void {
                    ++$callbackCount;
                }
            )
        );

        static::assertSame(\count($users), $callbackCount);
    }

    #[Depends('testPath')]
    public function testTransform(): void
    {
        $tester = new DataTester('{"key":"value"}');

        static::assertNotSame(
            $tester,
            $newTester = $tester->assertJson()->transform('json_decode')
        );

        static::assertInstanceOf(DataTester::class, $newTester);
        $newTester->path('key')->assertSame('value');
    }

    public function testAssertThat(): void
    {
        (new DataTester(1))->assertThat(new IsIdentical(1));
    }
}
