<?php

namespace App;

use App\Test\TestKernelBrowser;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('test.client')) {
            $container
                ->getDefinition('test.client')
                ->setClass(TestKernelBrowser::class)
                ->setAutowired(true);
        }
    }
}
