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
     * @var string
     *
     * @Assert\NotBlank()
     * @JMS\Type("string")
     */
    public $name;

    /**
     * A short description for the tag.
     * GFM syntax can be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * Additional external documentation for this tag.
     *
     * @var ExternalDocumentation
     *
     * @JMS\Type("Draw\Component\OpenApi\Schema\ExternalDocumentation")
     * @JMS\SerializedName("externalDocs")
     */
    public $externalDocs;
}
