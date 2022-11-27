<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutoConfigureSubClassesCompilerClass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $adminPoolDefinition = $container->getDefinition('sonata.admin.pool');
        $adminClasses = $adminPoolDefinition->getArgument(3);

        foreach ($container->findTaggedServiceIds('sonata.admin.sub_class') as $serviceId => $tags) {
            $subClasses = [];
            foreach ($tags as $tag) {
                $subClasses = array_merge(
                    [$tag['label'] => $tag['sub_class']],
                    $subClasses
                );
            }

            $container
                ->getDefinition($serviceId)
                ->addMethodCall('setSubClasses', [$subClasses]);

            foreach (array_unique($subClasses) as $subClass) {
                $adminClasses[$subClass][] = $serviceId;
            }
        }

        $adminPoolDefinition->setArgument(3, $adminClasses);
    }
}
