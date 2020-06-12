<?php

namespace Draw\Bundle\DashboardBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ExpressionLanguage extends BaseExpressionLanguage
{
    private $authorizationChecker;

    /**
     * @required
     */
    public function setAuthenticationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    private function buildValues(array $values): array
    {
        $values['auth_checker'] = $this->authorizationChecker;

        return $values;
    }

    private function buildNames(array $names)
    {
        return array_merge(
            $names,
            array_keys($this->buildValues([]))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate($expression, $values = [])
    {
        $values = $this->buildValues($values);

        return $this->parse($expression, array_keys($values))->getNodes()->evaluate($this->functions, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function parse($expression, $names)
    {
        $names = $this->buildNames($names);

        return parent::parse($expression, $names);
    }

    /**
     * {@inheritdoc}
     */
    public function compile($expression, $names = [])
    {
        $names = $this->buildNames($names);

        return parent::compile($expression, $names);
    }

    protected function registerFunctions()
    {
        parent::registerFunctions();

        $this->register('is_granted', function ($attributes, $object = 'null') {
            return sprintf('$auth_checker->isGranted(%s, %s)', $attributes, $object);
        }, function (array $variables, $attributes, $object = null) {
            return $variables['auth_checker']->isGranted($attributes, $object);
        });
    }
}
