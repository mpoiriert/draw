<?php

namespace Draw\Component\DependencyInjection\Integration;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PrependIntegrationInterface extends IntegrationInterface
{
    public function prepend(ContainerBuilder $container, array $config): void;
}
