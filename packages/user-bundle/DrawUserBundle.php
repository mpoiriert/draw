<?php

namespace Draw\Bundle\UserBundle;

use Draw\Bundle\UserBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new UserCheckerDecoratorPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION
        );
    }
}
