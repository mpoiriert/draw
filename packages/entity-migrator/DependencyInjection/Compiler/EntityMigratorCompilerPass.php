<?php

namespace Draw\Component\EntityMigrator\DependencyInjection\Compiler;

use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\Migrator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class EntityMigratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $container->findDefinition(Migrator::class);
        } catch (ServiceNotFoundException) {
            return;
        }

        $references = [];
        foreach (array_keys($container->findTaggedServiceIds(MigrationInterface::class)) as $id) {
            $name = $container
                ->getDefinition($id)
                ->getClass()::getName()
            ;

            $references[$name] = new Reference($id);
        }

        $container
            ->getDefinition('draw.entity_migrator.migrator')
            ->setArgument(
                '$migrations',
                ServiceLocatorTagPass::register($container, $references)
            )
            ->setArgument(
                '$migrationNames',
                array_keys($references)
            )
        ;
    }
}
