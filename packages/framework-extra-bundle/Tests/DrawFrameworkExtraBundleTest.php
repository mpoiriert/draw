<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawFrameworkExtraBundleTest extends TestCase
{
    private DrawFrameworkExtraBundle $bundle;

    public function setUp(): void
    {
        $this->bundle = new DrawFrameworkExtraBundle();
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
