<?php

namespace Draw\Bundle\SonataExtraBundle\Doctrine\Filter;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;

class InFilter extends Filter
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

        $values = explode(',', $data['value']);
        $values = array_filter(array_map('trim', $values));

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($query);

        $this->applyWhere($query, sprintf('%s.%s IN (:%s)', $alias, $field, $parameterName));
        $query->getQueryBuilder()->setParameter($parameterName, $values);
    }

    public function getDefaultOptions(): array
    {
        return ['advanced_filter' => false];
    }

    public function getRenderSettings(): array
    {
        return [ChoiceType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
