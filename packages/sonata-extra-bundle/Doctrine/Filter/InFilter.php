<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\Filter;

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;

class InFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        $value = $data->getValue();

        if (empty($value)) {
            return;
        }

        $value = trim((string) $value);

        if (0 === mb_strlen($value)) {
            return;
        }

        $values = explode(',', $value);
        $values = array_filter(array_map('trim', $values));

        $parameterName = $this->getNewParameterName($query);

        $this->applyWhere($query, \sprintf('%s.%s IN (:%s)', $alias, $field, $parameterName));
        $query->getQueryBuilder()->setParameter($parameterName, $values);
    }

    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ];
    }

    public function getDefaultOptions(): array
    {
        return ['advanced_filter' => false];
    }
}
