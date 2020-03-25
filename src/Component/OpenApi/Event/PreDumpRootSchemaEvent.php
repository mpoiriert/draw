<?php namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Schema\Root;
use Symfony\Contracts\EventDispatcher\Event;

class PreDumpRootSchemaEvent extends Event
{
    private $schema;

    public function __construct(Root $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return Root
     */
    public function getSchema()
    {
        return $this->schema;
    }
}