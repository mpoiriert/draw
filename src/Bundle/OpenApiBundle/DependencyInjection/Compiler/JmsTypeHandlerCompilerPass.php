<?php

namespace Draw\Bundle\OpenApiBundle\DependencyInjection\Compiler;

use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class JmsTypeHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $propertiesExtractor = $container->getDefinition(PropertiesExtractor::class);

        foreach (array_keys($container->findTaggedServiceIds(TypeToSchemaHandlerInterface::class)) as $id) {
            $propertiesExtractor->addMethodCall('registerTypeToSchemaHandler', [new Reference($id)]);
        }
    }
}
