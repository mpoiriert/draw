<?php

namespace Draw\Component\Profiling\Sql;

use Draw\Component\Tester\DataTester;

final class SqlAssertionBuilder
{
    /**
     * @var ?callable
     */
    private $countAssertion = null;

    /**
     * @param int|null $count The exact count of query expected
     *
     * @return SqlAssertionBuilder
     */
    public static function create(?int $count = null): self
    {
        $builder = new self();
        if (null !== $count) {
            $builder->assertCountEquals($count);
        }

        return $builder;
    }

    public function assertCountGreaterThanOrEqual($count): void
    {
        $this->countAssertion = ['assertGreaterThanOrEqual', $count];
    }

    public function assertCountLessThanOrEqual($count): void
    {
        $this->countAssertion = ['assertLessThanOrEqual', $count];
    }

    public function assertCountEquals($count): void
    {
        $this->countAssertion = ['assertEquals', $count];
    }

    public function __invoke(DataTester $tester)
    {
        if (!$this->countAssertion) {
            throw new \RuntimeException('No assertion configured.');
        }

        if ($tester->isReadable(SqlProfiler::PROFILER_TYPE)) {
            $tester->path(SqlProfiler::PROFILER_TYPE)->test($this);

            return;
        }

        $message = "Queries:\n".implode("\n", $tester->path('queries')->getData());

        [$method, $count] = $this->countAssertion;

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
