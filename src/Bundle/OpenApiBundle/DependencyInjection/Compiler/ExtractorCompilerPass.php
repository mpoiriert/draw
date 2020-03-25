<?php namespace Draw\Bundle\OpenApiBundle\DependencyInjection\Compiler;

use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExtractorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $openApi = $container->getDefinition(OpenApi::class);

        foreach (array_keys($container->findTaggedServiceIds(ExtractorInterface::class)) as $id) {
            $openApi->addMethodCall("registerExtractor", [new Reference($id)]);
        }
    }
}