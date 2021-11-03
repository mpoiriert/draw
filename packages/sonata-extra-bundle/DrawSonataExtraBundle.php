<?php

namespace Draw\Bundle\SonataExtraBundle;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler\ExtractArgumentFromDefaultValueCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawSonataExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new ExtractArgumentFromDefaultValueCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1
        );
    }
}
