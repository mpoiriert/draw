<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\Filter;

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;

class RelativeDateTimeFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $value = trim($data->getValue());

        if (0 === mb_strlen($value)) {
            return;
        }

        switch ($data->getType() ?? $this->getOption('default_operator')) {
            case NumberOperatorType::TYPE_EQUAL:
                $operator = '=';
                break;
            case NumberOperatorType::TYPE_GREATER_EQUAL:
                $operator = '>=';
                break;
            case NumberOperatorType::TYPE_GREATER_THAN:
                $operator = '>';
                break;
            case NumberOperatorType::TYPE_LESS_THAN:
                $operator = '<';
                break;
            case NumberOperatorType::TYPE_LESS_EQUAL:
            default:
                $operator = '<=';
                break;
        }

        $inputValue = date('Y-m-d H:i:s', strtotime($value));
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
