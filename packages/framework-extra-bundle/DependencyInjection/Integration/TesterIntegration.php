<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class TesterIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'tester';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $container->setDefinition(
            'draw.tester.command.tests_coverage_check_command',
            new Definition(TestsCoverageCheckCommand::class)
        )
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $container->setAlias(TestsCoverageCheckCommand::class, 'draw.tester.command.tests_coverage_check_command');
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
