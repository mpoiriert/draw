<?php
//example-start: TestClass
namespace Your\Project\Name;

use Draw\DataTester\AssertionBuilder;
use PHPUnit\Framework\TestCase;
use Draw\DataTester\Tester;

class ExampleTest extends TestCase
{
    public function test()
    {
        $dataToTest = 'A string value';

        $tester = new Tester($dataToTest);
        $tester
            ->assertInternalType('string')
            ->assertSame('A string value');
    }

//example-end: TestClass

    public function testConciseNew()
    {
        //example-start: ConciseNew
        (new Tester('A string value'))
            ->assertInternalType('string')
            ->assertSame('A string value');
        //example-end: ConciseNew
    }

    public function testPath()
    {
        //example-start: TestPath
        (new Tester((object)["key" => "value"]))
            ->path('key',
                function (Tester $tester) {
                    $tester->assertSame('value');
                }
            );
        //example-end: TestPath
    }

    public function testChainPath()
    {
        //example-start: ChainTestPath
        (new Tester((object)["key1" => "value1", "key2" => "value2"]))
            ->path('key1',
                function (Tester $tester) {
                    $tester->assertSame('value1');
                }
            )
            ->path('key2',
                function (Tester $tester) {
                    $tester->assertSame('value2');
                }
            );
        //example-end: ChainTestPath
    }

    public function testDeeperPath()
    {
        //example-start: DeeperPathTest
        (new Tester((object)["level1" => (object)["level2" => "value"]]))
            ->path('level1',
                function (Tester $tester) {
                    $tester->path('level2',
                        function (Tester $tester) {
                            $tester->assertSame('value');
                        }
                    );
                }
            );
        //example-end: DeeperPathTest
    }

    public function testEach()
    {
        //example-start: EachTest
        (new Tester(['value1', 'value2']))
            ->each(
                function (Tester $tester) {
                    $tester->assertInternalType('string');
                }
            );
        //example-end: EachTest
    }

    public function testTransform()
    {
        //example-start: Transform
        (new Tester('{"key":"value"}'))
            ->transform('json_decode',
                function (Tester $tester) {
                    $tester->path('key',
                        function (Tester $tester) {
                            $tester->assertSame('value');
                        }
                    );
                }
            );
        //example-end: Transform
    }

    public function testTransformAssert()
    {
        //example-start: AssertTransform
        (new Tester('{"key":"value"}'))
            ->assertJson()
            ->transform('json_decode',
                function (Tester $tester) {
                    $tester->path('key',
                        function (Tester $tester) {
                            $tester->assertSame('value');
                        }
                    );
                }
            );
        //example-end: AssertTransform
    }

    public function testTransformAssertCustom()
    {
        //example-start: AssertTransformCustom
        (new Tester('{"key":"value"}'))
            ->assertJson()
            ->transform(
                function($data) {
                    return json_decode($data, true);
                },
                function (Tester $tester) {
                    $tester->path('[key]',
                        function (Tester $tester) {
                            $tester->assertSame('value');
                        }
                    );
                }
            );
        //example-end: AssertTransformCustom
    }

    public function testIfPathIsReadable()
    {
        //example-start: IfPathIsReadable
        (new Tester(null))
            ->ifPathIsReadable(
                'notExistingPath',
                function (Tester $tester) {
                    //Will not be call with current data to test
                }
            );
        //example-end: IfPathIsReadable
        $this->assertTrue(true);//This is to prevent PHPUnit to flag test as risky
    }

    public function testIfPathIsReadableAndEach()
    {
        //example-start: IfPathIsReadableAndEach
        $users = [
            (object)[
                'firstName' => 'Martin',
                'active' => true,
                'referral' => 'Google'
            ],
            (object)[
                'firstName' => 'Julie',
                'active' => false
            ]
        ];
        (new Tester($users))
            ->each(
                function (Tester $tester) {
                    $tester
                        ->path(
                            'firstName',
                            function (Tester $tester) {
                                $tester->assertInternalType('string');
                            }
                        )
                        ->path(
                            'active',
                            function (Tester $tester) {
                                $tester->assertInternalType('boolean');
                            }
                        )
                        ->ifPathIsReadable(
                            'referral',
                            function (Tester $tester) {
                                $tester->assertInternalType('string');
                            }
                        );
                }
            );
        //example-end: IfPathIsReadableAndEach
    }

    public function testUser()
    {
        //example-start: TestWithClassCallable
        $user = (object)[
            'firstName' => 'Martin',
            'active' => true,
            'referral' => 'Google'
        ];

        (new Tester($user))
            ->test(new UserDataTester());
        //example-end: TestWithClassCallable
    }

    public function testUsers()
    {
        //example-start: EachWithClassCallableEach
        $users = [
            (object)[
                'firstName' => 'Martin',
                'active' => true,
                'referral' => 'Google'
            ],
            (object)[
                'firstName' => 'Julie',
                'active' => false
            ]
        ];

        (new Tester($users))
            ->each(new UserDataTester());
        //example-end: EachWithClassCallableEach
    }

    public function testWithAssertionBuilder()
    {
        //example-start: WithAssertionBuilder
        (new Tester((object)["level1" => (object)["level2" => "value"]]))
            ->path('level1.level2', (new AssertionBuilder())->assertSame('value'));
        //example-end: WithAssertionBuilder
    }
}

//example-start: UserDataTester
class UserDataTester
{
    public function __invoke(Tester $tester)
    {
        $tester
            ->path(
                'firstName',
                function (Tester $tester) {
                    $tester->assertInternalType('string');
                }
            )
            ->path(
                'active',
                function (Tester $tester) {
                    $tester->assertInternalType('boolean');
                }
            )
            ->ifPathIsReadable(
                'referral',
                function (Tester $tester) {
                    $tester->assertInternalType('string');
                }
            );
    }
}
//example-end: UserDataTester