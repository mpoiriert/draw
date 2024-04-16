<?php

declare(strict_types=1);

namespace Draw\Component\CronJob;

use Draw\Component\CronJob\Entity\CronJob;
use Draw\Component\CronJob\Entity\CronJobExecution;

class CronJobExecutionFactory
{
    public function create(CronJob $cronJob, bool $force = false): CronJobExecution
    {
        return (new CronJobExecution())
            ->setCronJob($cronJob)
            ->setRequestedAt(new \DateTimeImmutable())
            ->setForce($force);
    }
}
