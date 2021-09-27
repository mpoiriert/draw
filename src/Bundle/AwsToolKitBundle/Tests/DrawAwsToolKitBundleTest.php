<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests;

use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleListener;
use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DrawAwsToolKitBundleTest extends KernelTestCase
{
    use ServiceTesterTrait;

    protected static $class = AppKernel::class;

    public function testCloudWatchLogsDownloadCommand()
    {
        $this->assertTrue(
            $this
                ->getService(CloudWatchLogsDownloadCommand::class)
                ->getDefinition()
                ->hasOption(NewestInstanceRoleListener::OPTION_AWS_NEWEST_INSTANCE_ROLE)
        );
    }
}
