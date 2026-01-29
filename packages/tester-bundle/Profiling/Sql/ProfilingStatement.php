<?php

namespace Draw\Bundle\TesterBundle\Profiling\Sql;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;

/**
 * @internal
 */
final class ProfilingStatement extends AbstractStatementMiddleware
{
    /** @var array<int|string, mixed> */
    private array $params = [];

    /** @var array<int|string, ParameterType|int> */
    private array $types = [];

    public function __construct(
        StatementInterface $statement,
        private QueryCollector $queryCollector,
        private string $sql,
    ) {
        parent::__construct($statement);
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): void
    {
        $this->params[$param] = $value;
        $this->types[$param] = $type;

        parent::bindValue($param, $value, $type);
    }

    public function execute(): Result
    {
        $this->queryCollector->startQuery($this->sql, $this->params, $this->types);

        try {
            return parent::execute();
        } finally {
            $this->queryCollector->stopQuery();
        }
    }
}
