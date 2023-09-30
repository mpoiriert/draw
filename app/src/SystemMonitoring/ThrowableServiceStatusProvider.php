<?php

namespace App\SystemMonitoring;

use Draw\Component\Application\SystemMonitoring\ServiceStatus;
use Draw\Component\Application\SystemMonitoring\ServiceStatusProviderInterface;
use Draw\Component\Application\SystemMonitoring\Status;

class ThrowableServiceStatusProvider implements ServiceStatusProviderInterface
{
    public function getServiceStatuses(array $options = []): iterable
    {
        yield new ServiceStatus(
            'throwable',
            Status::ERROR,
            [new \Exception('From an exception')]
        );
    }
}
