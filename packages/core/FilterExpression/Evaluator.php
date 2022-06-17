<?php

namespace Draw\Component\Core\FilterExpression;

use Draw\Component\Core\FilterExpression\Expression\CompositeExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpressionEvaluator;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\Core\FilterExpression\Expression\ExpressionEvaluator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

class Evaluator
{
    private $serviceProvider;

    public function __construct(?ServiceProviderInterface $serviceProvider = null)
    {
        $this->serviceProvider = $serviceProvider ?: new ServiceLocator([
            ConstraintExpressionEvaluator::class => fn () => new ConstraintExpressionEvaluator(),
            CompositeExpressionEvaluator::class => fn () => new CompositeExpressionEvaluator($this),
        ]);
    }

    /**
     * @return array Return the filtered data
     */
    public function execute(Query $query, iterable $data): iterable
    {
        $expression = $query->getExpression();
        foreach ($data as $row) {
            if ($this->evaluate($row, $expression)) {
                yield $row;
            }
        }
    }

    public function evaluate($data, Expression $expression): bool
    {
        /** @var ExpressionEvaluator $expressionEvaluator */
        $expressionEvaluator = $this->serviceProvider->get($expression->evaluateBy());

        return $expressionEvaluator->evaluate($data, $expression);
    }
}
