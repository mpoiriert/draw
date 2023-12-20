<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Security\Core\Security;
use Draw\Component\Workflow\EventListener\AddTransitionNameToContextListener;
use Draw\Component\Workflow\EventListener\AddUserToContextListener;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class WorkflowIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'workflow';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Workflow\\',
            \dirname(
                (new \ReflectionClass(AddTransitionNameToContextListener::class))->getFileName(),
                2
            ),
        );

        if (!class_exists(Security::class)) {
            $container->removeDefinition(AddUserToContextListener::class);
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.workflow.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
