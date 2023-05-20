<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Schema\Root;

class CleanEvent
{
    public function __construct(private Root $rootSchema)
    {
    }

    public function getRootSchema(): Root
    {
        return $this->rootSchema;
    }

    public function setRootSchema(Root $rootSchema): void
    {
        $this->rootSchema = $rootSchema;
    }
}
