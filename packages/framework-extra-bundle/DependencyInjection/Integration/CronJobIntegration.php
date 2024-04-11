<?php

declare(strict_types=1);

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Entity\Execution;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class CronJobIntegration implements IntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'cron-job';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\CronJob\\',
            $directory = \dirname(
                (new \ReflectionClass(PurgeExecutionCommand::class))->getFileName(),
                2
            ),
            [
                $directory.'/Output/',
            ]
        );

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.cron_job.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }

    public function prepend(ContainerBuilder $container, array $config): void
    {
        $this->assertHasExtension($container, 'doctrine');

        $reflection = new \ReflectionClass(Execution::class);

        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'DrawCronJob' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => \dirname($reflection->getFileName()),
                            'prefix' => $reflection->getNamespaceName(),
                        ],
                    ],
                ],
            ]
        );
    }
}
