<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Event;

use Draw\Component\CronJob\Entity\CronJobExecution;
use Symfony\Contracts\EventDispatcher\Event;

class PostCronJobExecutionEvent extends Event
{
    public function __construct(
        private CronJobExecution $execution,
    ) {
    }

    public function getExecution(): CronJobExecution
    {
        return $this->execution;
    }
}
