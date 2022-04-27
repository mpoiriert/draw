<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Schema\Root;
use Symfony\Contracts\EventDispatcher\Event;

class PreDumpRootSchemaEvent extends Event
{
    private Root $schema;

    public function __construct(Root $schema)
    {
        $this->schema = $schema;
    }

    public function getSchema(): Root
    {
        return $this->schema;
    }
}
