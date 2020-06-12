<?php

namespace Draw\Bundle\CronBundle\Tests;

use Draw\Bundle\CronBundle\CronManager;
use Draw\Bundle\CronBundle\Model\Job;
use PHPUnit\Framework\TestCase;

class CronManagerTest extends TestCase
{
    /**
     * @var CronManager
     */
    private $cronManager;

    public function setUp(): void
    {
        $this->cronManager = new CronManager();
    }

    public function testDumpJobs(): void
    {
        $job = new Job('Job name', 'echo "test"');
        $this->cronManager->addJob($job);
        $cronTab = <<<CRONTAB
#Description: 
* * * * * echo "test" >/dev/null 2>&1

CRONTAB;

        $this->assertSame(
            $cronTab,
            $this->cronManager->dumpJobs()
        );
    }

    public function testDumpJobs_twoJobs(): void
    {
        $job = new Job('Job name', 'echo "test"');
        $this->cronManager->addJob($job);

        $job = new Job('Job 2', 'echo "test"', '*/5 * * * *', true, 'Job every 5 minutes');
        $this->cronManager->addJob($job);
        $cronTab = <<<CRONTAB
#Description: 
* * * * * echo "test" >/dev/null 2>&1

#Description: Job every 5 minutes
*/5 * * * * echo "test" >/dev/null 2>&1

CRONTAB;

        $this->assertSame(
            $cronTab,
            $this->cronManager->dumpJobs()
        );
    }

    public function testDumpJobs_disabled(): void
    {
        $job = new Job('Job 2', 'echo "test"');
        $job->setEnabled(false);
        $this->cronManager->addJob($job);

        $this->assertSame(
            PHP_EOL,
            $this->cronManager->dumpJobs()
        );
    }
}
