<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Doctrine\Persistence\ManagerRegistry;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Draw\DoctrineExtra\ORM\Query\CommentSqlWalker;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DoctrineExtraIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'doctrine_extra';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->loadORM($config, $loader, $container);
    }

    private function loadORM(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\DoctrineExtra\\ORM\\',
            \dirname((new \ReflectionClass(EntityHandler::class))->getFileName()),
        );

        if (!interface_exists(ManagerRegistry::class)) {
            $container->removeDefinition(EntityHandler::class);
        }

        $container->removeDefinition(CommentSqlWalker::class);

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.doctrine_extra.orm.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
