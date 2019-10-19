<?php namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawAwsToolKitExtensionTest extends TestCase
{
    /**
     * @var DrawAwsToolKitExtension
     */
    private $extension;

    public function setUp()
    {
        $this->extension = new DrawAwsToolKitExtension();
    }

    public function provideTestHasServiceDefinition()
    {
        return [
            [CloudWatchLogsDownloadCommand::class],
            [NewestInstanceRoleListener::class]
        ];
    }

    /**
     * @dataProvider provideTestHasServiceDefinition
     *
     * @param $id
     */
    public function testHasServiceDefinition($id)
    {
        $this->assertTrue($this->load([])->hasDefinition($id));
    }

    /**
     * @param array $config
     * @return ContainerBuilder
     */
    private function load(array $config)
    {
        $containerBuilder = new ContainerBuilder();
        $this->extension->load($config, $containerBuilder);
        return $containerBuilder;
    }
}