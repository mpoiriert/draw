<?php

namespace Draw\Bundle\MessengerBundle;

use Draw\Bundle\MessengerBundle\DependencyInjection\Compiler\BrokerCompilerPass;
use Draw\Bundle\MessengerBundle\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawMessengerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new MessengerTransportNamesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1
        );

        $container->addCompilerPass(new BrokerCompilerPass());
    }
}
