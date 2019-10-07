<?php
//example-start: TestClass
namespace Your\Project\Name;

use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test()
    {
        $dataToTest = 'A string value';

        $tester = new DataTester($dataToTest);
        $tester
            ->assertSame('A string value');
    }

//example-end: TestClass

    public function testConciseNew()
    {
        //example-start: ConciseNew
        (new DataTester('A string value'))
            ->assertSame('A string value');
        //example-end: ConciseNew
    }

    public function testPath()
    {
        //example-start: TestPath
        (new DataTester((object)["key" => "value"]))
            ->path('key')
            ->assertSame('value');
        //example-end: TestPath
    }

    public function testChainPath()
    {
        //example-start: ChainTestPath
        $tester = new DataTester((object)["key1" => "value1", "key2" => "value2"]);
        $tester->path('key1')->assertSame('value1');
        $tester->path('key2')->assertSame('value2');
        //example-end: ChainTestPath
    }

    public function testDeeperPath()
    {
        //example-start: DeeperPathTest
        (new DataTester((object)["level1" => (object)["level2" => "value"]]))
            ->path('level1')
            ->path('level2')->assertSame('value');
        //example-end: DeeperPathTest
    }

    public function testEach()
    {
        //example-start: EachTest
        (new DataTester(['value1', 'value2']))
            ->each(
                function (DataTester $tester) {
                    $tester->assertIsString();
                }
            );
        //example-end: EachTest
    }

    public function testTransform()
    {
        //example-start: Transform
        (new DataTester('{"key":"value"}'))
            ->transform('json_decode')
            ->path('key')->assertSame('value');
        //example-end: Transform
    }

    public function testTransformAssert()
    {
        //example-start: AssertTransform
        (new DataTester('{"key":"value"}'))
            ->assertJson()
            ->transform('json_decode')
            ->path('key')->assertSame('value');
        //example-end: AssertTransform
    }

    public function testTransformAssertCustom()
    {
        //example-start: AssertTransformCustom
        (new DataTester('{"key":"value"}'))
            ->assertJson()
            ->transform(
                function ($data) {
                    return json_decode($data, true);
                }
            )->path('[key]')->assertSame('value');
        //example-end: AssertTransformCustom
    }

    public function testIfPathIsReadable()
    {
        //example-start: IfPathIsReadable
        (new DataTester(null))
            ->ifPathIsReadable(
                'notExistingPath',
                function (DataTester $tester) {
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
        (new DataTester($users))
            ->each(
                function (DataTester $tester) {
                    $tester->path('firstName')->assertIsString();
                    $tester->path('active')->assertIsBool();
                    $tester->ifPathIsReadable(
                        'referral',
                        function (DataTester $tester) {
                            $tester->assertIsString();
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

        (new DataTester($user))
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

        (new DataTester($users))
            ->each(new UserDataTester());
        //example-end: EachWithClassCallableEach
    }
}

//example-start: UserDataTester
class UserDataTester
{
    public function __invoke(DataTester $tester)
    {
        $tester->path('firstName')->assertIsString();
        $tester->path('active')->assertIsBool();
        $tester->ifPathIsReadable(
            'referral',
            function (DataTester $tester) {
                $tester->assertIsString();
            }
        );
    }
}
//example-end: UserDataTester