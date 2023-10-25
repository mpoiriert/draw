<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\EntityMigrator\MigrationInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EntityMigratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $references = [];
        foreach (array_keys($container->findTaggedServiceIds(MigrationInterface::class)) as $id) {
            $name = $container
                ->getDefinition($id)
                ->getClass()::getName();

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
            );
    }
}
