<?php

namespace Draw\Component\OpenApi\Request\ValueResolver;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestBody
{
    public string $argumentName;

    public function __construct(
        public ?string $type = null,
        public bool $validate = true,
        public ?array $validationGroups = null,
        public ?array $deserializationGroups = null,
        public ?array $propertiesMap = null,
        public array $deserializationContext = [],
        public array $options = [],
    ) {
    }
}
