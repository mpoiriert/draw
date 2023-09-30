<?php

namespace App\SystemMonitoring;

use Draw\Component\Application\SystemMonitoring\ServiceStatus;
use Draw\Component\Application\SystemMonitoring\ServiceStatusProviderInterface;
use Draw\Component\Application\SystemMonitoring\Status;

class PredefinedServiceStatusProvider implements ServiceStatusProviderInterface
{
    private Status $status;

    public function __construct(string $status = 'OK')
    {
        $this->status = Status::from($status);
    }

    public function getServiceStatuses(array $options = []): iterable
    {
        yield new ServiceStatus(
            'predefined',
            $this->status
        );
    }
}
