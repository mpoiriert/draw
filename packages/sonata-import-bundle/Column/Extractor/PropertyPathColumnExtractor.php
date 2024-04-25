<?php

namespace Draw\Bundle\SonataImportBundle\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class PropertyPathColumnExtractor extends BaseColumnExtractor
{
    public static function getDefaultPriority(): int
    {
        return -1000;
    }

    public function __construct(private ?PropertyAccessor $propertyAccessor = null)
    {
        $this->propertyAccessor ??= PropertyAccess::createPropertyAccessor();
    }

    #[\Override]
    public function assign(object $object, Column $column, mixed $value): bool
    {
        $this->propertyAccessor->setValue($object, $column->getMappedTo(), $value);

        return true;
    }
}
