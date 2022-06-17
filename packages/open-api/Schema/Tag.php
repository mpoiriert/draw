<?php

namespace Draw\Component\OpenApi\Schema;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Tag
{
    /**
     * The name of the tag.
     *
     * @Assert\NotBlank
     */
    public ?string $name = null;

    /**
     * A short description for the tag.
     * GFM syntax can be used for rich text representation.
     */
    public ?string $description = null;

    /**
     * Additional external documentation for this tag.
     *
     * @JMS\SerializedName("externalDocs")
     */
    public ?ExternalDocumentation $externalDocs = null;
}
