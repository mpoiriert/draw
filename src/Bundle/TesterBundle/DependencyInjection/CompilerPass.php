<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $evaluator = $container->getDefinition('draw.tester.expression_filter');

        $expressionEvaluators = [];

        foreach ($container->findTaggedServiceIds(ExpressionEvaluator::class) as $id => $tags) {
            $expressionEvaluators[$id] = new Reference($id);
        }

        $evaluator
            ->setArgument(
                '$serviceProvider',
                ServiceLocatorTagPass::register($container, $expressionEvaluators)
            );
    }
}
