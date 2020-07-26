<?php namespace Draw\Bundle\OpenApiBundle\DependencyInjection\Compiler;

use Draw\Bundle\OpenApiBundle\JmsSerializer\Construction\DoctrineObjectConstructor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JmsDoctrineObjectConstructionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if(null === $container->getDefinition('jms_serializer.doctrine_object_constructor')->getDecoratedService()) {
            return;
        }

        $container->removeDefinition('jms_serializer.object_constructor');
        $container->setAlias('jms_serializer.doctrine_object_constructor', DoctrineObjectConstructor::class);
        $container->setAlias('jms_serializer.object_constructor', 'jms_serializer.doctrine_object_constructor');
    }
}