<?php

namespace Draw\Component\OpenApi\Schema;

#[\Attribute(\Attribute::TARGET_METHOD)]
class PathParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        string $type = 'string',
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->required = true;
    }
}
