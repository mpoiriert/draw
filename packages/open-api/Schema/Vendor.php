<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as Serializer;

/**
 * Annotation use for documenting via extractor. This is not directly use in the schema itself.
 *
 * @Annotation
 */
class Vendor implements VendorInterface
{
    /**
     * @Serializer\Exclude()
     */
    public ?string $name = null;

    /**
     * @var mixed
     */
    public $value;

    public function getVendorName(): string
    {
        return $this->name;
    }

    public function allowClassLevelConfiguration(): bool
    {
        return true;
    }
}
