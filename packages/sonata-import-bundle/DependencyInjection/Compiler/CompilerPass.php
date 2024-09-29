<?php

namespace Draw\Bundle\SonataImportBundle\DependencyInjection\Compiler;

use Draw\Bundle\SonataImportBundle\Extension\ImportExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $classes = $container->getParameter('draw.sonata_import.classes');

        foreach ($container->findTaggedServiceIds('sonata.admin') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['model_class'])) {
                    continue;
                }

                if (!isset($classes[$tag['model_class']])) {
                    continue;
                }

                $container->getDefinition($serviceId)
                    ->addMethodCall('addExtension', [new Reference(ImportExtension::class)])
                ;

                break;
            }
        }
    }
}
