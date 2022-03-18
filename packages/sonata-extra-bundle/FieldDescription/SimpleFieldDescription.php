<?php

namespace Draw\Bundle\SonataExtraBundle\FieldDescription;

use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

class SimpleFieldDescription extends BaseFieldDescription
{
    public static function create($name, $value, $options = []): self
    {
        $fieldDescription = new static();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);
        $fieldDescription->options['value'] = $value;
        $fieldDescription->options['safe'] = true;

        return $fieldDescription;
    }

    public function setAssociationMapping($associationMapping)
    {
    }

    public function getTargetEntity()
    {
    }

    public function getTargetModel()
    {
    }

    public function setFieldMapping($fieldMapping)
    {
    }

    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
    }

    public function isIdentifier()
    {
        return false;
    }

    public function getValue($object)
    {
        return $this->options['value'];
    }
}
