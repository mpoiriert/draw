<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataImportBundle\Exception;

use Draw\Bundle\SonataImportBundle\Entity\Column;

class EntityClassNotSetException extends \InvalidArgumentException
{
    public function __construct(Column $column)
    {
        parent::__construct(\sprintf('Entity class is not set on column "%s"', $column->getHeaderName()));
    }
}
