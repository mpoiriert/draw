<?php

namespace Draw\Component\AwsToolKit\DependencyInjection;

use Draw\Component\AwsToolKit\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Component\AwsToolKit\EventListener\NewestInstanceRoleCheckListener;
use Draw\Component\AwsToolKit\Imds\ImdsClientInterface;
use Draw\Component\AwsToolKit\Imds\ImdsClientV1;
use Draw\Component\AwsToolKit\Imds\ImdsClientV2;
use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class AwsToolKitIntegration implements IntegrationInterface, ContainerBuilderIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'aws_tool_kit';
    }

    public function buildContainer(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddNewestInstanceRoleCommandOptionPass());
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\Component\AwsToolKit\\',
            \dirname(
                (new \ReflectionClass(ImdsClientInterface::class))->getFileName(),
                2
            )
        );

        switch ($config['imds_version']) {
            case 1:
                $container->removeDefinition(ImdsClientV2::class);
                $container->setAlias(ImdsClientInterface::class, ImdsClientV1::class);
                break;
            case 2:
                $container->removeDefinition(ImdsClientV1::class);
                $container->setAlias(ImdsClientInterface::class, ImdsClientV2::class);
                break;
            default:
                $container->removeDefinition(ImdsClientV2::class);
                $container->removeDefinition(ImdsClientV1::class);
                break;
        }

        if (!$this->isConfigEnabled($container, $config['newest_instance_role_check'])) {
            $container->removeDefinition(NewestInstanceRoleCheckListener::class);
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.aws_tool_kit.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->validate()
                ->ifTrue(static fn (array $config) => match (true) {
                    !$config['newest_instance_role_check']['enabled'], null !== $config['imds_version'] => false,
                    default => true,
                })
                ->thenInvalid('You must define a imds_version since you enabled newest_instance_role_check')
            ->end()
            ->children()
                ->enumNode('imds_version')->values([1, 2, null])->defaultNull()->end()
                ->arrayNode('newest_instance_role_check')
                    ->canBeEnabled()
                ->end()
            ->end()
        ;
    }
}
