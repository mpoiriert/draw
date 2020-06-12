<?php

namespace Draw\Bundle\DashboardBundle\ExpressionLanguage;

use Draw\Bundle\DashboardBundle\Action\ButtonExecutionCheck;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class SerializerFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            ExpressionFunction::fromPhp('Draw\Bundle\DashboardBundle\first_parent', 'first_parent'),
            new ExpressionFunction('can_activate', function ($buttonArg, $parentArg) {
                return sprintf(
                    '$this->get(%s)->canExecute(%s, %s)',
                    ButtonExecutionCheck::class,
                    $buttonArg,
                    $parentArg
                );
            }, function (array $variables, $button, $action) {
                return $variables['container']
                    ->get(ButtonExecutionCheck::class)
                    ->canExecute($button, $action);
            }),
        ];
    }
}
