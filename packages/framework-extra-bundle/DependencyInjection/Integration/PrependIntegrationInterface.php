<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PrependIntegrationInterface extends IntegrationInterface
{
    public function prepend(ContainerBuilder $container, array $config): void;
}
