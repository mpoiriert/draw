<?php

namespace Draw\Bundle\TesterBundle\DependencyInjection;

use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
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
            )
        ;

        foreach ($container->findTaggedServiceIds('messenger.receiver') as $receiverId => $tags) {
            $transportTester = (new Definition(TransportTester::class))
                ->setArgument('$transport', new Reference($receiverId))
                ->setArgument('$evaluator', new Reference('draw.tester.expression_filter'))
                ->setPublic(true)
            ;

            $container->setDefinition($receiverId.'.draw.tester', $transportTester);
        }
    }
}
