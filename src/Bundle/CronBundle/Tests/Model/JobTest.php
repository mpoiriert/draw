<?php

namespace Draw\Bundle\CronBundle\Tests\Model;

use Draw\Bundle\CronBundle\Model\Job;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    private const DEFAULT_NAME = 'name';
    private const DEFAULT_COMMAND = 'ls';

    /**
     * @var Job
     */
    private $job;

    public function setUp(): void
    {
        $this->job = new Job(self::DEFAULT_NAME, self::DEFAULT_COMMAND);
    }

    public function testGetName(): void
    {
        $this->assertSame(self::DEFAULT_NAME, $this->job->getName());
    }

    public function testGetDescription(): void
    {
        $this->assertSame('', $this->job->getDescription());
    }

    public function testGetCommand(): void
    {
        $this->assertSame(self::DEFAULT_COMMAND, $this->job->getCommand());
    }

    public function testGetEnabled(): void
    {
        $this->assertSame(true, $this->job->getEnabled());
    }

    public function testGetOutPut(): void
    {
        $this->assertSame('>/dev/null 2>&1', $this->job->getOutput());
    }

    public function testGetExpression(): void
    {
        $this->assertSame('* * * * *', $this->job->getExpression());
    }

    public function testToArray(): void
    {
        $this->assertEquals(
            [
                'name' => 'name',
                'description' => '',
                'expression' => '* * * * *',
                'command' => 'ls',
                'enabled' => true,
                'output' => '>/dev/null 2>&1',
            ],
            $this->job->toArray()
        );
    }
}
