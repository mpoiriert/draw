<?php namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlAssertionBuilder;
use Draw\Component\Profiling\Sql\SqlMetric;
use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class SqlAssertionBuilderTest extends TestCase
{
    private $assertionBuilder;

    public function setUp(): void
    {
        $this->assertionBuilder = new SqlAssertionBuilder();
    }

    public function provideTestAssertCountEquals()
    {
        yield [0, new DataTester((object)['sql' => new SqlMetric([])]), false];
        yield [1, new DataTester((object)['sql' => new SqlMetric(['query'])]), false];
        yield [1, new DataTester((object)['sql' => new SqlMetric([])]), true];
        yield [0, new DataTester((object)['sql' => new SqlMetric(['query'])]), true];
    }

    /**
     * @dataProvider provideTestAssertCountEquals
     *
     * @param $expectedCount
     * @param DataTester $dataTester
     * @param $shouldFail
     */
    public function testProvideAssertCountEquals($expectedCount, DataTester $dataTester, $shouldFail)
    {
        $this->assertionBuilder->assertCountEquals($expectedCount);
        $this->invoke($dataTester, $shouldFail);
    }

    public function provideTestAssertCountGreaterThanOrEqual()
    {
        yield [0, new DataTester((object)['sql' => new SqlMetric([])]), false];
        yield [0, new DataTester((object)['sql' => new SqlMetric(['query'])]), false];
        yield [1, new DataTester((object)['sql' => new SqlMetric(['query'])]), false];
        yield [1, new DataTester((object)['sql' => new SqlMetric([])]), true];
    }

    /**
     * @dataProvider provideTestAssertCountGreaterThanOrEqual
     *
     * @param $expectedCount
     * @param DataTester $dataTester
     * @param $shouldFail
     */
    public function testAssertCountGreaterThanOrEqual($expectedCount, DataTester $dataTester, $shouldFail)
    {
        $this->assertionBuilder->assertCountGreaterThanOrEqual($expectedCount);
        $this->invoke($dataTester, $shouldFail);
    }

    public function provideTestAssertCountLessThanOrEqual()
    {
        yield [1, new DataTester((object)['sql' => new SqlMetric([])]), false];
        yield [0, new DataTester((object)['sql' => new SqlMetric([])]), false];
        yield [1, new DataTester((object)['sql' => new SqlMetric(['query'])]), false];
        yield [0, new DataTester((object)['sql' => new SqlMetric(['query'])]), true];
    }

    /**
     * @dataProvider provideTestAssertCountLessThanOrEqual
     *
     * @param $expectedCount
     * @param DataTester $dataTester
     * @param $shouldFail
     */
    public function testAssertCountLessThanOrEqual($expectedCount, DataTester $dataTester, $shouldFail)
    {
        $this->assertionBuilder->assertCountLessThanOrEqual($expectedCount);
        $this->invoke($dataTester, $shouldFail);
    }

    public function testInvoke_failMessage()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Queries: 
SELECT * FROM test
SELECT * FROM test2
Failed asserting that 2 matches expected 0.');

        (new DataTester(new SqlMetric(['SELECT * FROM test', 'SELECT * FROM test2'])))
            ->test(SqlAssertionBuilder::create(0));
    }

    private function invoke(DataTester $dataTester, $shouldFail)
    {
        $exception = null;
        try {
            $this->assertionBuilder->__invoke($dataTester);
        } catch (ExpectationFailedException $exception) {

        } finally {
            if ($shouldFail) {
                $this->assertInstanceOf(ExpectationFailedException::class, $exception);
            }
        }
    }
}