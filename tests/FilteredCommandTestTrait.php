<?php

namespace App\Tests;

use Draw\Component\Tester\Application\CommandTestTrait;

trait FilteredCommandTestTrait
{
    use CommandTestTrait;

    protected function filterDefinitionOptions(array $options): array
    {
        unset(
            $options['aws-newest-instance-role'],
            $options['draw-execution-id'],
            $options['draw-execution-ignore'],
            $options['draw-post-execution-queue-cron-job']
        );

        return $options;
    }
}
