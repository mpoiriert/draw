<?php

namespace Draw\Component\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionFinder
{
    /**
     * @return iterable<Definition>
     */
    public static function findConsoleCommandDefinitions(ContainerBuilder $container, bool $ignoreAbstract = true): iterable
    {
        foreach (array_keys($container->findTaggedServiceIds('console.command')) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            if ($ignoreAbstract && $definition->isAbstract()) {
                continue;
            }

            yield $definition;
        }
    }
}
