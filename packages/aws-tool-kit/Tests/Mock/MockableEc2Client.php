<?php

declare(strict_types=1);

namespace Draw\Component\AwsToolKit\Tests\Mock;

use Aws\Ec2\Ec2Client;
use Aws\Result;

class MockableEc2Client extends Ec2Client
{
    public function describeInstances(array $args = []): Result
    {
        return parent::describeInstances($args);
    }
}
