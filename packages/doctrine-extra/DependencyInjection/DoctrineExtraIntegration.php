<?php

namespace Draw\DoctrineExtra\DependencyInjection;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
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
        $this->loadORM($config['orm'], $loader, $container);
        $this->loadMongoODM($config['mongodb_odm'], $loader, $container);
    }

    private function loadORM(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container
            ->registerAliasForArgument('doctrine', ManagerRegistry::class, 'ormManagerRegistry');

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

    private function loadMongoODM(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container
            ->registerAliasForArgument('doctrine_mongodb', ManagerRegistry::class, 'odmManagerRegistry');
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->append($this->createORMNode())
                ->append($this->createMongoODMNode())
            ->end();
    }

    private function createMongoODMNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('mongodb_odm');

        ContainerBuilder::willBeAvailable('doctrine/mongodb-odm', DocumentManager::class, [])
            ? $node->canBeDisabled()
            : $node->canBeEnabled();

        return $node;
    }

    private function createORMNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('orm');

        ContainerBuilder::willBeAvailable('doctrine/orm', EntityManagerInterface::class, [])
            ? $node->canBeDisabled()
            : $node->canBeEnabled();

        return $node;
    }
}
