<?php namespace Draw\Bundle\DashboardBundle\DependencyInjection\Compiler;

use Draw\Bundle\DashboardBundle\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Draw\Bundle\DashboardBundle\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExpressionFunctionProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registryDefinition = $container->findDefinition(ExpressionLanguage::class);

        foreach (array_keys($container->findTaggedServiceIds(ExpressionFunctionProviderInterface::class)) as $id) {
            $registryDefinition->addMethodCall('registerProvider', [new Reference($id)]);
        }
    }
}
