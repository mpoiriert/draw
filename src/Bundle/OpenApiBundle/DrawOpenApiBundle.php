<?php namespace Draw\Bundle\OpenApiBundle;

use Draw\Bundle\OpenApiBundle\DependencyInjection\Compiler\ExtractorCompilerPass;
use Draw\Bundle\OpenApiBundle\DependencyInjection\Compiler\JmsTypeHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawOpenApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtractorCompilerPass());
        $container->addCompilerPass(new JmsTypeHandlerCompilerPass());
    }
}