<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests;

use Draw\Bundle\AwsToolKitBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Bundle\AwsToolKitBundle\DependencyInjection\DrawAwsToolKitExtension;
use Draw\Bundle\AwsToolKitBundle\DrawAwsToolKitBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\DrawAwsToolKitBundle
 */
class DrawAwsToolKitBundleTest extends TestCase
{
    private DrawAwsToolKitBundle $bundle;

    public function setUp(): void
    {
        $this->bundle = new DrawAwsToolKitBundle();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testGetContainerExtension(): void
    {
        $this->assertInstanceOf(DrawAwsToolKitExtension::class, $this->bundle->getContainerExtension());
    }

    public function testBuild(): void
    {
        $this->bundle->build($containerBuilder = new ContainerBuilder());

        $result =
            array_filter(
                $containerBuilder->getCompilerPassConfig()->getBeforeOptimizationPasses(),
                function ($value) {
                    return $value instanceof AddNewestInstanceRoleCommandOptionPass;
                }
            );

        $this->assertCount(
            1,
            $result,
            sprintf('[%s] must be added', AddNewestInstanceRoleCommandOptionPass::class)
        );
    }
}
