<?php

namespace Draw\Profiling\Bridge\Laravel4;

use Draw\Profiling\Sql\SqlProfiler;
use Draw\Profiling\Sql\SqlLog;
use Illuminate\Events\Dispatcher;

class Laravel4SqlProfiler extends SqlProfiler
{
    private $started = false;

    public function __construct(Dispatcher $dispatcher)
    {
        $dispatcher->listen('illuminate.query', [$this, 'logQuery']);
    }

    public function isStarted()
    {
        return $this->started;
    }

    public function start()
    {
        $this->started = true;
    }

    public function logQuery($query, $bindings, $time, $name)
    {
        if(!$this->started) {
            return;
        }
        
        if($bindings) {
            // Format binding data for sql insertion
            foreach ($bindings as $i => $binding)
            {
                if ($binding instanceof \DateTime)
                {
                    $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                }
                else if (is_string($binding))
                {
                    $bindings[$i] = "'$binding'";
                }
            }

            $realQuery = str_replace(array('%', '?'), array('%%', '%s'), $query);
            $realQuery = vsprintf($realQuery, $bindings);
        } else {
            $realQuery = $query;
        }

        $this->getMetricBuilder()->addLog(
            new SqlLog($realQuery)
        );
    }

    /**
     * @return \Draw\Profiling\Sql\SqlMetric
     */
    public function stop()
    {
        $result = parent::stop();
        $this->started = false;

        return $result;
    }
}