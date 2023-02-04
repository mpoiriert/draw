<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Schema\Root;
use Symfony\Contracts\EventDispatcher\Event;

class PreDumpRootSchemaEvent extends Event
{
    public function __construct(private Root $schema)
    {
    }

    public function getSchema(): Root
    {
        return $this->schema;
    }
}
