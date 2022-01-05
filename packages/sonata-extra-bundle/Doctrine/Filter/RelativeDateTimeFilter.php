<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;

class RelativeDateTimeFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, $alias, $field, $data): void
    {
        if (!$data || !\is_array($data) || !\array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);

        if (0 === mb_strlen($data['value'])) {
            return;
        }

        $operator = '<=';
        switch ($data['type'] ?? $this->getOption('default_operator')) {
            case NumberOperatorType::TYPE_EQUAL:
                $operator = '=';
                break;
            case NumberOperatorType::TYPE_GREATER_EQUAL:
                $operator = '>=';
                break;
            case NumberOperatorType::TYPE_GREATER_THAN:
                $operator = '>';
                break;
            case NumberOperatorType::TYPE_LESS_EQUAL:
                $operator = '<=';
                break;
            case NumberOperatorType::TYPE_LESS_THAN:
                $operator = '<';
                break;
        }

        $inputValue = date('Y-m-d H:i:s', strtotime($data['value']));
        $parameterName = $this->getNewParameterName($query);
        $completeField = sprintf('%s.%s', $alias, $field);
        $this->applyWhere(
            $query,
            sprintf('%s %s :%s', $completeField, $operator, $parameterName)
        );

        $query->getQueryBuilder()->setParameter($parameterName, $inputValue);
    }

    public function getDefaultOptions(): array
    {
        return [
            'operator_type' => NumberOperatorType::class,
            'operator_options' => [],
            'default_operator' => NumberOperatorType::TYPE_LESS_EQUAL,
        ];
    }

    public function getRenderSettings(): array
    {
        return [
            DefaultType::class,
            [
                'label' => $this->getLabel(),
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
            ],
        ];
    }
}
