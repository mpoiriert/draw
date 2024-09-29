<?php

namespace Draw\Component\Profiling\Tests\Sql;

use Draw\Component\Profiling\Sql\SqlLog;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SqlLogTest extends TestCase
{
    public function test(): void
    {
        $log = new SqlLog('query');
        static::assertSame('query', $log->query);
    }
}
