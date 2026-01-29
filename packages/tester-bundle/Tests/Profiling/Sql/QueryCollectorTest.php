<?php

namespace Draw\Bundle\TesterBundle\Tests\Profiling\Sql;

use Draw\Bundle\TesterBundle\Profiling\Sql\QueryCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(QueryCollector::class)]
class QueryCollectorTest extends TestCase
{
    private QueryCollector $queryCollector;

    protected function setUp(): void
    {
        $this->queryCollector = new QueryCollector();
    }

    public function testInitialState(): void
    {
        static::assertFalse($this->queryCollector->isEnabled());
        static::assertSame([], $this->queryCollector->getQueries());
    }

    public function testStart(): void
    {
        $this->queryCollector->start();

        static::assertTrue($this->queryCollector->isEnabled());
    }

    public function testStop(): void
    {
        $this->queryCollector->start();
        $this->queryCollector->stop();

        static::assertFalse($this->queryCollector->isEnabled());
    }

    public function testStartResetsQueries(): void
    {
        $this->queryCollector->start();
        $this->queryCollector->startQuery('SELECT 1');
        $this->queryCollector->stopQuery();

        static::assertCount(1, $this->queryCollector->getQueries());

        $this->queryCollector->start();

        static::assertSame([], $this->queryCollector->getQueries());
    }

    public function testStartQueryWhenDisabled(): void
    {
        $this->queryCollector->startQuery('SELECT 1');

        static::assertSame([], $this->queryCollector->getQueries());
    }

    public function testStartQueryWhenEnabled(): void
    {
        $this->queryCollector->start();
        $this->queryCollector->startQuery('SELECT * FROM users', ['id' => 1], ['id' => 'integer']);

        $queries = $this->queryCollector->getQueries();

        static::assertCount(1, $queries);
        static::assertSame('SELECT * FROM users', $queries[0]['sql']);
        static::assertSame(['id' => 1], $queries[0]['params']);
        static::assertSame(['id' => 'integer'], $queries[0]['types']);
        static::assertSame(0, $queries[0]['executionMS']);
    }

    public function testStopQueryCalculatesExecutionTime(): void
    {
        $this->queryCollector->start();
        $this->queryCollector->startQuery('SELECT 1');

        usleep(1000); // 1ms

        $this->queryCollector->stopQuery();

        $queries = $this->queryCollector->getQueries();

        static::assertGreaterThan(0, $queries[0]['executionMS']);
    }

    public function testStopQueryWhenDisabled(): void
    {
        $this->queryCollector->stopQuery();

        static::assertSame([], $this->queryCollector->getQueries());
    }

    public function testStopQueryWithoutStart(): void
    {
        $this->queryCollector->start();
        $this->queryCollector->stopQuery();

        static::assertSame([], $this->queryCollector->getQueries());
    }

    public function testMultipleQueries(): void
    {
        $this->queryCollector->start();

        $this->queryCollector->startQuery('SELECT 1');
        $this->queryCollector->stopQuery();

        $this->queryCollector->startQuery('SELECT 2');
        $this->queryCollector->stopQuery();

        $this->queryCollector->startQuery('SELECT 3');
        $this->queryCollector->stopQuery();

        $queries = $this->queryCollector->getQueries();

        static::assertCount(3, $queries);
        static::assertSame('SELECT 1', $queries[0]['sql']);
        static::assertSame('SELECT 2', $queries[1]['sql']);
        static::assertSame('SELECT 3', $queries[2]['sql']);
    }

    public function testReset(): void
    {
        $this->queryCollector->start();
        $this->queryCollector->startQuery('SELECT 1');
        $this->queryCollector->stopQuery();

        static::assertCount(1, $this->queryCollector->getQueries());

        $this->queryCollector->reset();

        static::assertSame([], $this->queryCollector->getQueries());
        static::assertTrue($this->queryCollector->isEnabled());
    }
}
