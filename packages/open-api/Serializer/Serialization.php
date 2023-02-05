<?php

namespace Draw\Component\OpenApi\Serializer;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Serialization
{
    public function __construct(
        public ?int $statusCode = null,
        public ?array $serializerGroups = [],
        public ?bool $serializerEnableMaxDepthChecks = null,
        public ?string $serializerVersion = null,
        public array $contextAttributes = [],
        public array $options = [],
    ) {
    }
}
