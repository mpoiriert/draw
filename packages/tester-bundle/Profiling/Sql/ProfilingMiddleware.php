<?php

namespace Draw\Bundle\TesterBundle\Profiling\Sql;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;

/**
 * DBAL Middleware for SQL profiling in tests.
 */
final class ProfilingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private QueryCollector $queryCollector,
    ) {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new ProfilingDriver($driver, $this->queryCollector);
    }

    public function getQueryCollector(): QueryCollector
    {
        return $this->queryCollector;
    }
}
