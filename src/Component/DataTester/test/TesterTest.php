<?php

namespace Draw\DataTester;

use PHPUnit\Framework\TestCase;

class TesterTest extends TestCase
{
    public function testAssertPathIsNotReadable()
    {
        $tester = new Tester(null);

        $this->assertSame(
            $tester,
            $tester->assertPathIsNotReadable('toto')
        );
    }


    public function testAssertPathIsReadable()
    {
        $tester = new Tester((object)["key" => "value"]);

        $this->assertSame(
            $tester,
            $tester->assertPathIsReadable('key')
        );
    }

    public function testPath()
    {
        $tester = new Tester((object)["key" => "value"]);

        $this->assertSame(
            $tester,
            $tester->path('key'),
            'Return value of path must be the object itself for the fluent interface.'
        );

        $callableHasBeenCalled = false;

        $tester->path('key',
            function (Tester $pathTester) use ($tester, &$callableHasBeenCalled) {
                $callableHasBeenCalled = true;
                $this->assertNotSame(
                    $tester,
                    $pathTester,
                    'A new tester for the path must be pass to callable.');

                $this->assertEquals(
                    'value',
                    $pathTester->getData(),
                    'Path tester must have the path data in it.');
            }
        );

        $this->assertTrue($callableHasBeenCalled, 'Path callable has not been called.');
    }

    /**
     * @depends testPath
     */
    public function testChain()
    {
        $data = new \stdClass();
        $data->key1 = 'value1';
        $data->key2 = ['arrayValue0', 'arrayValue1'];

        $callbackCount = 0;
        (new Tester($data))
            ->assertPathIsNotReadable('[key1]', 'The data is a object, should not be read like a array.')
            ->path(
                'key1',
                function (Tester $tester) use (&$callbackCount) {
                    $callbackCount++;
                    $tester->assertSame('value1');
                }
            )
            ->path(
                'key2',
                function (Tester $tester) use (&$callbackCount) {
                    $callbackCount++;
                    $tester
                        ->assertInternalType('array')
                        ->assertCount(2)
                        ->path(
                            '[0]',
                            function (Tester $tester) use (&$callbackCount) {
                                $callbackCount++;
                                $tester->assertSame('arrayValue0');
                            }
                        );
                }
            )
            ->path(
                'key2[1]',
                function (Tester $tester) use (&$callbackCount) {
                    $callbackCount++;
                    $tester->assertSame('arrayValue1');
                }
            );

        $this->assertSame(4, $callbackCount);
    }

    public function testIfPathIsReadable()
    {
        $hasBeenCalled = false;

        (new Tester(null))
            ->ifPathIsReadable(
                'toto',
                function (Tester $tester) use (&$hasBeenCalled) {
                    $hasBeenCalled = true;
                }
            );

        $this->assertFalse($hasBeenCalled, 'The path is not readable the callable should not have been called.');
    }

    /**
     * @depends testIfPathIsReadable
     * @depends testPath
     */
    public function testEach()
    {
        $users = [
            ['firstName' => 'Martin', 'active' => true, 'referral' => 'Google'],
            ['firstName' => 'Julie', 'active' => false]
        ];

        $callbackCount = 0;

        (new Tester($users))
            ->each(
                function (Tester $tester) use (&$callbackCount) {
                    $callbackCount++;
                    $tester
                        ->path(
                            '[firstName]',
                            function (Tester $tester) {
                                $tester->assertInternalType('string');
                            }
                        )
                        ->path(
                            '[active]',
                            function (Tester $tester) {
                                $tester->assertInternalType('boolean');
                            }
                        )
                        ->ifPathIsReadable(
                            '[referral]',
                            function (Tester $tester) {
                                $tester->assertInternalType('string');
                            }
                        );
                }
            );

        $this->assertSame(count($users), $callbackCount);
    }

    public function testTransform()
    {
        $callbackCount = 0;

        (new Tester('{"key":"value"}'))
            ->assertJson()
            ->transform('json_decode',
                function (Tester $tester) use (&$callbackCount) {
                    $tester->path(
                        'key',
                        function (Tester $tester) use (&$callbackCount) {
                            $callbackCount++;
                            $tester->assertEquals('value');
                        }
                    );
                }
            );

        $this->assertSame(1, $callbackCount);
    }
}