<?php

namespace Draw\Bundle\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExcludeDoctrineEntitiesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('doctrine.orm.default_annotation_metadata_driver')) {
            return;
        }

        $container->getDefinition('doctrine.orm.default_annotation_metadata_driver')
            ->addMethodCall(
                'addExcludePaths',
                [$container->getParameter('draw.user.orm.default_annotation_metadata_driver.exclude_paths')]
            );
    }
}
