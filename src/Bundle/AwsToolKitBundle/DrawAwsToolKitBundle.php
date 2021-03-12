<?php

namespace Draw\Bundle\AwsToolKitBundle;

use Draw\Bundle\AwsToolKitBundle\DependencyInjection\Compiler\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawAwsToolKitBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
    }
}
