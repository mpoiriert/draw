<?php namespace Draw\Bundle\TesterBundle\Profiling;

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\LoggerChain;
use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\Profiling\Sql\SqlLog;
use TypeError;

/**
 * SqlProfiler for Symfony use by Draw\Component\Profiling\Sql namespace to do metric calculation of SQL in integration tests.
 */
class SqlProfiler extends \Draw\Component\Profiling\Sql\SqlProfiler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SQLLogger
     */
    private $logger;

    /**
     * @var DebugStack
     */
    private $debugLogger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function start()
    {
        $configuration = $this->entityManager->getConnection()->getConfiguration();
        $this->logger = $configuration->getSQLLogger();

        $this->debugLogger = new DebugStack();

        if(!is_null($this->logger)) {
            $logger = new LoggerChain();
            $logger->addLogger($this->debugLogger);
            $logger->addLogger($this->logger);
            $configuration->setSQLLogger($logger);
        } else {
            $configuration->setSQLLogger($this->debugLogger);
        }
    }

    public function stop()
    {
        $metricBuilder = $this->getMetricBuilder();
        foreach($this->debugLogger->queries as $query) {
            $query = $this->sanitizeQuery($query);
            $sql = $query['sql'];
            if ($query['explainable']) {
                foreach ($query['params'] as $value) {
                    if (is_array($value)) {
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

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger($this->logger);

        return parent::stop();
    }

    private function sanitizeQuery($query)
    {
        $query['explainable'] = true;
        if (null === $query['params']) {
            $query['params'] = array();
        }
        if (!is_array($query['params'])) {
            $query['params'] = array($query['params']);
        }
        foreach ($query['params'] as $j => $param) {
            if (isset($query['types'][$j])) {
                // Transform the param according to the type
                $type = $query['types'][$j];
                if (is_string($type)) {
                    $type = Type::getType($type);
                }
                if ($type instanceof Type) {
                    $query['types'][$j] = $type->getBindingType();
                    try {
                        $param = $type->convertToDatabaseValue($param,  $this->entityManager->getConnection()->getDatabasePlatform());
                    } catch (TypeError $e) {
                        // Error thrown while processing params, query is not explainable.
                        $query['explainable'] = false;
                    } catch (ConversionException $e) {
                        $query['explainable'] = false;
                    }
                }
            }

            list($query['params'][$j], $explainable) = $this->sanitizeParam($param);
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
     *
     * @param mixed $var
     *
     * @return array
     */
    private function sanitizeParam($var): array
    {
        if (is_object($var)) {
            $className = get_class($var);

            return method_exists($var, '__toString') ?
                array(sprintf('/* Object(%s): */"%s"', $className, $var->__toString()), false) :
                array(sprintf('/* Object(%s) */', $className), false);
        }

        if (is_array($var)) {
            $a = array();
            $original = true;
            foreach ($var as $k => $v) {
                list($value, $orig) = $this->sanitizeParam($v);
                $original = $original && $orig;
                $a[$k] = $value;
            }

            return array($a, $original);
        }

        if (is_resource($var)) {
            return array(sprintf('/* Resource(%s) */', get_resource_type($var)), false);
        }

        return array($var, true);
    }
}