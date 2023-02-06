<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as Serializer;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class QueryParameter extends Parameter
{
    public function __construct(
        ?string $name = null,
        ?string $type = null,
        ?string $collectionFormat = null,
        #[Serializer\Exclude]
        public array $constraints = []
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->collectionFormat = $collectionFormat;
    }
}
