<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\Console\EventListener\DocumentationIgnoredCommandEventListener;
use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Console\EventListener\CommandFlowListener;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ConsoleIntegration implements IntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'console';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Console\\',
            $directory = \dirname(
                (new \ReflectionClass(PurgeExecutionCommand::class))->getFileName(),
                2
            ),
            [
                $directory.'/Output/',
            ]
        );

        $container
            ->getDefinition(CommandFlowListener::class)
            ->setArgument('$ignoreDisabledCommand', $config['ignore_disabled_command']);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.console.'
        );

        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Bundle\\FrameworkExtraBundle\\Console\\',
            \dirname((new \ReflectionClass(DrawFrameworkExtraBundle::class))->getFileName()).'/Console'
        );

        $container
            ->getDefinition(DocumentationIgnoredCommandEventListener::class)
            ->setArgument('$ignoredCommandNames', $config['documentation']['ignored_commands']);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.console.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->booleanNode('ignore_disabled_command')->defaultFalse()->end()
                ->arrayNode('documentation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('ignored_commands')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
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
                        'DrawConsole' => [
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
