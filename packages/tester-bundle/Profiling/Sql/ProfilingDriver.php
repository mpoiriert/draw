<?php

namespace Draw\Bundle\TesterBundle\Profiling\Sql;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * @internal
 */
final class ProfilingDriver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private QueryCollector $queryCollector,
    ) {
        parent::__construct($driver);
    }

    public function connect(
        #[\SensitiveParameter]
        array $params,
    ): ProfilingConnection {
        return new ProfilingConnection(
            parent::connect($params),
            $this->queryCollector,
        );
    }
}
