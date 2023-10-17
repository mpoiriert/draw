<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlAssertionBuilder;
use Draw\Component\Profiling\Sql\SqlMetric;
use Draw\Component\Tester\DataTester;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class SqlAssertionBuilderTest extends TestCase
{
    private SqlAssertionBuilder $assertionBuilder;

    protected function setUp(): void
    {
        $this->assertionBuilder = new SqlAssertionBuilder();
    }

    public static function provideTestAssertCountEquals(): iterable
    {
        yield [0, new DataTester((object) ['sql' => new SqlMetric([])]), false];
        yield [1, new DataTester((object) ['sql' => new SqlMetric(['query'])]), false];
        yield [1, new DataTester((object) ['sql' => new SqlMetric([])]), true];
        yield [0, new DataTester((object) ['sql' => new SqlMetric(['query'])]), true];
    }

    /**
     * @dataProvider provideTestAssertCountEquals
     */
    public function testProvideAssertCountEquals(int $expectedCount, DataTester $dataTester, bool $shouldFail): void
    {
        $this->assertionBuilder->assertCountEquals($expectedCount);
        $this->invoke($dataTester, $shouldFail);
    }

    public static function provideTestAssertCountGreaterThanOrEqual(): \Generator
    {
        yield [0, new DataTester((object) ['sql' => new SqlMetric([])]), false];
        yield [0, new DataTester((object) ['sql' => new SqlMetric(['query'])]), false];
        yield [1, new DataTester((object) ['sql' => new SqlMetric(['query'])]), false];
        yield [1, new DataTester((object) ['sql' => new SqlMetric([])]), true];
    }

    /**
     * @dataProvider provideTestAssertCountGreaterThanOrEqual
     */
    public function testAssertCountGreaterThanOrEqual(int $expectedCount, DataTester $dataTester, bool $shouldFail): void
    {
        $this->assertionBuilder->assertCountGreaterThanOrEqual($expectedCount);
        $this->invoke($dataTester, $shouldFail);
    }

    public static function provideTestAssertCountLessThanOrEqual(): \Generator
    {
        yield [1, new DataTester((object) ['sql' => new SqlMetric([])]), false];
        yield [0, new DataTester((object) ['sql' => new SqlMetric([])]), false];
        yield [1, new DataTester((object) ['sql' => new SqlMetric(['query'])]), false];
        yield [0, new DataTester((object) ['sql' => new SqlMetric(['query'])]), true];
    }

    /**
     * @dataProvider provideTestAssertCountLessThanOrEqual
     */
    public function testAssertCountLessThanOrEqual(int $expectedCount, DataTester $dataTester, bool $shouldFail): void
    {
        $this->assertionBuilder->assertCountLessThanOrEqual($expectedCount);
        $this->invoke($dataTester, $shouldFail);
    }

    public function testInvokeFailMessage(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Queries:
SELECT * FROM test
SELECT * FROM test2
Failed asserting that 2 matches expected 0.');

        (new DataTester(new SqlMetric(['SELECT * FROM test', 'SELECT * FROM test2'])))
            ->test(SqlAssertionBuilder::create(0));
    }

    public function testInvokeNoAssertionException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No assertion configured.');

        (new DataTester(new SqlMetric([])))
            ->test(SqlAssertionBuilder::create());
    }

    private function invoke(DataTester $dataTester, $shouldFail): void
    {
        $exception = null;
        try {
            $this->assertionBuilder->__invoke($dataTester);
        } catch (ExpectationFailedException $exception) {
        } finally {
            if ($shouldFail) {
                static::assertInstanceOf(ExpectationFailedException::class, $exception);
            }
        }
    }
}
