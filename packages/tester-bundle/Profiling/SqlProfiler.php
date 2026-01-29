<?php

namespace Draw\Bundle\TesterBundle\Profiling;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\Profiling\Sql\QueryCollector;
use Draw\Component\Profiling\Sql\SqlLog;
use Draw\Component\Profiling\Sql\SqlMetric;

/**
 * SqlProfiler for Symfony use by Draw\Component\Profiling\Sql namespace to do metric calculation of SQL in integration tests.
 */
class SqlProfiler extends \Draw\Component\Profiling\Sql\SqlProfiler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QueryCollector $queryCollector,
    ) {
    }

    public function start(): void
    {
        $this->queryCollector->reset();
        $this->queryCollector->start();
    }

    public function stop(): SqlMetric
    {
        $this->queryCollector->stop();
        $metricBuilder = $this->getMetricBuilder();
        foreach ($this->queryCollector->getQueries() as $query) {
            $query = $this->sanitizeQuery($query);
            $sql = $query['sql'];
            if ($query['explainable']) {
                foreach ($query['params'] as $value) {
                    if (\is_array($value)) {
                        $tempValues = [];
                        foreach ($value as $tempValue) {
                            $tempValues[] = var_export($tempValue, true);
                        }
                        $value = implode(',', $tempValues);
                        $sql = implode($value, explode('?', $sql, 2));
                    } else {
                        $sql = implode(var_export($value, true), explode('?', $sql, 2));
                    }
                }
            }
            $metricBuilder->addLog(new SqlLog($sql));
        }

        return parent::stop();
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function sanitizeQuery(array $query): array
    {
        $query['explainable'] = true;
        if (null === $query['params']) {
            $query['params'] = [];
        }
        if (!\is_array($query['params'])) {
            $query['params'] = [$query['params']];
        }
        foreach ($query['params'] as $j => $param) {
            if (isset($query['types'][$j])) {
                // Transform the param according to the type
                $type = $query['types'][$j];
                if (\is_string($type)) {
                    $type = Type::getType($type);
                }
                if ($type instanceof Type) {
                    try {
                        $param = $type->convertToDatabaseValue(
                            $param,
                            $this->entityManager->getConnection()->getDatabasePlatform()
                        );
                    } catch (\TypeError) {
                        // Error thrown while processing params, query is not explainable.
                        $query['explainable'] = false;
                    } catch (ConversionException) {
                        $query['explainable'] = false;
                    }
                }
            }

            [$query['params'][$j], $explainable] = $this->sanitizeParam($param);
            if (!$explainable) {
                $query['explainable'] = false;
            }
        }

        return $query;
    }

    /**
     * Sanitizes a param.
     *
     * The return value is an array with the sanitized value and a boolean
     * indicating if the original value was kept (allowing to use the sanitized
     * value to explain the query).
     */
    private function sanitizeParam(mixed $var): array
    {
        if (\is_object($var)) {
            $className = $var::class;

            return method_exists($var, '__toString') ?
                [\sprintf('/* Object(%s): */"%s"', $className, $var->__toString()), false] :
                [\sprintf('/* Object(%s) */', $className), false];
        }

        if (\is_array($var)) {
            $a = [];
            $original = true;
            foreach ($var as $k => $v) {
                [$value, $orig] = $this->sanitizeParam($v);
                $original = $original && $orig;
                $a[$k] = $value;
            }

            return [$a, $original];
        }

        if (\is_resource($var)) {
            return [\sprintf('/* Resource(%s) */', get_resource_type($var)), false];
        }

        return [$var, true];
    }
}
