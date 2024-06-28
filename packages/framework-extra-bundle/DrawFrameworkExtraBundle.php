<?php

namespace Draw\Bundle\FrameworkExtraBundle;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\DrawFrameworkExtraExtension;
use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $containerExtension = $this->getContainerExtension();

        \assert($containerExtension instanceof DrawFrameworkExtraExtension);

        foreach ($containerExtension->getIntegrations() as $integration) {
            if ($integration instanceof ContainerBuilderIntegrationInterface) {
                $integration->buildContainer($container);
            }
        }

    }
}
