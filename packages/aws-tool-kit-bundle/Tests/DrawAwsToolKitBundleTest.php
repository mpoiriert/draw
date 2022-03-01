<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests;

use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleListener;
use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DrawAwsToolKitBundleTest extends KernelTestCase
{
    use ServiceTesterTrait;

    // For symfony 4.x
    protected static $class = AppKernel::class;

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

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
