<?php

declare(strict_types=1);

namespace Draw\Component\AwsToolKit\Tests\Mock;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Result;

class MockableCloudWatchLogsClient extends CloudWatchLogsClient
{
    public function getLogEvents(array $args = []): Result
    {
        return parent::getLogEvents($args);
    }
}
