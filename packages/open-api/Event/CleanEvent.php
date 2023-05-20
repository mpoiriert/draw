<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Schema\Root;

class CleanEvent
{
    private Root $rootSchema;

    public function __construct(Root $rootSchema)
    {
        $this->setRootSchema($rootSchema);
    }

    public function getRootSchema(): Root
    {
        return $this->rootSchema;
    }

    public function setRootSchema(Root $rootSchema): void
    {
        // This is to "clone" the object recursively
        $this->rootSchema = unserialize(serialize($rootSchema));
    }
}
