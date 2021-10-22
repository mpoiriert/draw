<?php

namespace Draw\Bundle\PostOfficeBundle\Tests;

use Draw\Bundle\PostOfficeBundle\DependencyInjection\CompilerPass;
use Draw\Bundle\PostOfficeBundle\DrawPostOfficeBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawPostOfficeBundleTest extends TestCase
{
    public function testBuild()
    {
        $bundle = new DrawPostOfficeBundle();
        $bundle->build($container = new ContainerBuilder());

        $found = false;
        foreach ($container->getCompiler()->getPassConfig()->getBeforeOptimizationPasses() as $compilerPass) {
            if ($compilerPass instanceof CompilerPass) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Compiler pass ['.CompilerPass::class.'] not added.');
    }
}
