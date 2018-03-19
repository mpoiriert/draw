<?php

namespace Draw\Profiling\Sql;

use Draw\DataTester\Tester;

class SqlAssertionBuilder
{
    private $countAssertion;

    /**
     * @param null|int $count The exact count of query expected
     * @return SqlAssertionBuilder
     */
    public static function create($count = null)
    {
        $builder = new static();
        if(!is_null($count)) {
            $builder->assertCountEquals($count);
        }

        return $builder;
    }

    public function assertCountGreaterThanOrEqual($count)
    {
        $this->countAssertion = ['assertGreaterThanOrEqual', $count];
    }

    public function assertCountLessThanOrEqual($count)
    {
        $this->countAssertion = ['assertLessThanOrEqual', $count];
    }

    public function assertCountEquals($count)
    {
        $this->countAssertion = ['assertEquals', $count];
    }

    public function __invoke(Tester $tester)
    {
        if(!$this->countAssertion) {
            return;
        }

        if($tester->isReadable(SqlProfiler::PROFILER_TYPE)) {
            return $tester->path(SqlProfiler::PROFILER_TYPE)->test($this);
        }

        $message = "Queries: \n" . implode("\n", $tester->path('queries')->getData());

        list($method, $count) = $this->countAssertion;

        $countTester = $tester->path('count');

        switch ($method) {
            case 'assertGreaterThanOrEqual':
                $countTester->assertGreaterThanOrEqual($count, $message);
                break;
            case 'assertLessThanOrEqual':
                $countTester->assertLessThanOrEqual($count, $message);
                break;
            case 'assertEquals':
                $countTester->assertEquals($count, $message);
                break;
        }
    }
}