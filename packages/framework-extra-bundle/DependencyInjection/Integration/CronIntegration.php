<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Application\Command\CronDumpToFileCommand;
use Draw\Component\Application\Cron\CronManager;
use Draw\Component\Application\Cron\Job;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class CronIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'cron';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $directory = dirname(
            (new ReflectionClass(CronManager::class))->getFileName()
        );

        $definition = (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $exclude = [
            $directory.'/Job.php',
        ];

        $namespace = 'Draw\\Component\\Application\\Cron\\';

        $loader->registerClasses(
            $definition,
            $namespace,
            $directory,
            $exclude
        );

        $container->setDefinition(
            'draw.cron.command.dump_to_file',
            (clone $definition)
                ->setClass(CronDumpToFileCommand::class)
        );

        $container->setAlias(CronDumpToFileCommand::class, 'draw.cron.command.dump_to_file');

        $cronManagerDefinition = $container->getDefinition(CronManager::class);
        foreach ($config['jobs'] as $jobData) {
            $jobDefinition = new Definition(
                Job::class,
                [
                    $jobData['name'],
                    $jobData['command'],
                    $jobData['expression'],
                    $jobData['enabled'],
                    $jobData['description'],
                ]
            );

            $jobDefinition->addMethodCall('setOutput', [$jobData['output']]);

            $cronManagerDefinition->addMethodCall('addJob', [$jobDefinition]);
        }

        $this->renameDefinitions(
            $container,
            CronManager::class,
            'draw.cron.manager'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('jobs')
                    ->defaultValue([])
                    ->beforeNormalization()
                        ->always(function ($config) {
                            foreach ($config as $name => $configuration) {
                                if (!isset($configuration['name'])) {
                                    $config[$name]['name'] = $name;
                                }
                            }

                            return $config;
                        })
                    ->end()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->validate()
                                    ->ifTrue(function ($value) {
                                        return is_int($value);
                                    })
                                    ->thenInvalid('You must specify a name for the job. Can be via the attribute or the key.')
                                ->end()
                                ->isRequired()
                            ->end()
                            ->scalarNode('description')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('expression')
                                ->isRequired()
                            ->end()
                            ->scalarNode('output')
                                ->defaultValue('>/dev/null 2>&1')
                            ->end()
                            ->scalarNode('command')
                                ->isRequired()
                            ->end()
                            ->booleanNode('enabled')
                                ->defaultValue(true)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
