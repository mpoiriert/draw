<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\Console\EventListener\DocumentationFilterCommandEventListener;
use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Descriptor\TextDescriptor;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Console\EventListener\CommandFlowListener;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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

        if (!$config['documentation']['command_names']) {
            $container->removeDefinition(DocumentationFilterCommandEventListener::class);
        } else {
            $container
                ->getDefinition(DocumentationFilterCommandEventListener::class)
                ->setArgument('$commandNames', $config['documentation']['command_names'])
                ->setArgument('$filter', $config['documentation']['filter']);
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.console.'
        );

        $container
            ->setDefinition(
                'draw.console.descriptor_helper',
                new Definition(DescriptorHelper::class)
            )
            ->addMethodCall(
                'register',
                ['txt', new Reference(TextDescriptor::class)]
            );

        $container->setAlias(
            DescriptorHelper::class,
            'draw.console.descriptor_helper'
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
                        ->enumNode('filter')->values(['in', 'out'])->defaultValue('in')->end()
                        ->arrayNode('command_names')
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
