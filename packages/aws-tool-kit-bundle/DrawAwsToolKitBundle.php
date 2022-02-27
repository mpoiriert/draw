<?php

namespace Draw\Bundle\AwsToolKitBundle;

use Draw\Bundle\AwsToolKitBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawAwsToolKitBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddNewestInstanceRoleCommandOptionPass());
    }
}
