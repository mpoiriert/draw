<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as Serializer;

/**
 * Attribute use for documenting via extractor. This is not directly use in the schema itself.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Vendor
{
    /**
     * @Serializer\Exclude
     */
    public ?string $name = null;

    public mixed $value = null;

    public function __construct(string $name, mixed $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getVendorName(): string
    {
        return $this->name;
    }
}
