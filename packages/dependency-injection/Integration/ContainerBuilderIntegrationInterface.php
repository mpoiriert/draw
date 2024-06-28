<?php

namespace Draw\Component\DependencyInjection\Integration;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ContainerBuilderIntegrationInterface
{
    public function buildContainer(ContainerBuilder $container): void;
}
