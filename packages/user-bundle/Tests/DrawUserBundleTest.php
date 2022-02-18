<?php

namespace Draw\Bundle\UserBundle\Tests;

use Draw\Bundle\UserBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\UserBundle\DrawUserBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawUserBundleTest extends TestCase
{
    private $bundle;

    public function setUp(): void
    {
        $this->bundle = new DrawUserBundle();
    }

    public function testBuild(): void
    {
        $containerBuilder = new ContainerBuilder();
        $this->bundle->build($containerBuilder);

        $result =
            array_filter(
                $containerBuilder->getCompilerPassConfig()->getBeforeOptimizationPasses(),
                function ($value) {
                    return $value instanceof UserCheckerDecoratorPass;
                }
            );

        $this->assertCount(
            1,
            $result,
            sprintf('[%s] must be register', UserCheckerDecoratorPass::class)
        );
    }
}
