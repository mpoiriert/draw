<?php

namespace Draw\Bundle\TesterBundle\Profiling\Sql;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

/**
 * @internal
 */
final class ProfilingConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $connection,
        private QueryCollector $queryCollector,
    ) {
        parent::__construct($connection);
    }

    public function prepare(string $sql): DriverStatement
    {
        return new ProfilingStatement(
            parent::prepare($sql),
            $this->queryCollector,
            $sql,
        );
    }

    public function query(string $sql): Result
    {
        $this->queryCollector->startQuery($sql);

        try {
            return parent::query($sql);
        } finally {
            $this->queryCollector->stopQuery();
        }
    }

    public function exec(string $sql): int
    {
        $this->queryCollector->startQuery($sql);

        try {
            return parent::exec($sql);
        } finally {
            $this->queryCollector->stopQuery();
        }
    }
}
