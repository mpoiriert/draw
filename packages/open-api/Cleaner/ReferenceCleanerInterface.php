<?php

namespace Draw\Component\OpenApi\Cleaner;

use Draw\Component\OpenApi\Schema\Root;

interface ReferenceCleanerInterface
{
    /**
     * @return bool true if some reference where cleaned
     */
    public function cleanReferences(Root $rootSchema): bool;
}
