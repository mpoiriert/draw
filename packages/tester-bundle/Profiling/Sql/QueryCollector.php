<?php

namespace Draw\Bundle\TesterBundle\Profiling\Sql;

/**
 * Collects SQL queries for profiling purposes.
 */
class QueryCollector
{
    private bool $enabled = false;

    /** @var array<int, array{sql: string, params: array|null, types: array|null, executionMS: float}> */
    private array $queries = [];

    private ?float $start = null;

    public function start(): void
    {
        $this->enabled = true;
        $this->queries = [];
    }

    public function stop(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->start = microtime(true);
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
        ];
    }

    public function stopQuery(): void
    {
        if (!$this->enabled || null === $this->start) {
            return;
        }

        $idx = \count($this->queries) - 1;
        $this->queries[$idx]['executionMS'] = microtime(true) - $this->start;
        $this->start = null;
    }

    /**
     * @return array<int, array{sql: string, params: array|null, types: array|null, executionMS: float}>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    public function reset(): void
    {
        $this->queries = [];
        $this->start = null;
    }
}
