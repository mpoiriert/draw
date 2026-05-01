<?php

namespace Draw\Bundle\SonataImportBundle\Tests\Import\Fixtures;

use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class CallTrackingColumnExtractor extends BaseColumnExtractor
{
    public int $callCount = 0;

    public mixed $lastValue = null;

    #[\Override]
    public function assign(object $object, Column $column, mixed $value): bool
    {
        ++$this->callCount;
        $this->lastValue = $value;

        return true;
    }
}
